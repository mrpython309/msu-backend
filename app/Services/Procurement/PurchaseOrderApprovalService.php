<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\PurchaseOrderStatus;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateStep;
use App\Models\PoApprovalStep;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dynamic multi-level approval workflow for Purchase Orders.
 * Works exactly like the indent workflow but operates on purchase_orders / po_approval_steps.
 */
class PurchaseOrderApprovalService
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflow,
        private readonly ProcurementActivityLogger $logger,
        private readonly ProcurementNotificationService $notifications,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // Template resolution
    // ─────────────────────────────────────────────────────────────────

    public function resolveTemplate(PurchaseOrder $po): ApprovalTemplate
    {
        $categoryId = $po->workflowCategoryId();
        $categoryName = $categoryId
            ? (\App\Models\AssetCategory::query()->find($categoryId)?->category_name ?? 'this category')
            : 'this purchase order';

        $template = ApprovalTemplate::forPurchaseOrder($categoryId);

        if (! $template || $template->steps->isEmpty()) {
            throw new ProcurementException(
                'No active Purchase Order Approval Template is configured for '.$categoryName.'. '
                .'Ask an administrator to create one under Setting → Approval Templates '
                .'(Module: Purchase Order, Category: '.$categoryName.' or Default).'
            );
        }

        if (! $template->isFullyConfigured()) {
            throw new ProcurementException(
                'The PO approval template "'.$template->name.'" is incomplete for '.$categoryName.'. '
                .'Assign an approver for every level under Setting → Approval Templates.'
            );
        }

        $template->loadMissing(['steps.approver']);

        return $template;
    }

    // ─────────────────────────────────────────────────────────────────
    // Submit
    // ─────────────────────────────────────────────────────────────────

    public function submit(PurchaseOrder $po, User $actor, ?string $remarks = null): PurchaseOrder
    {
        $submitted = DB::transaction(function () use ($po, $actor, $remarks) {
            $locked = $this->lockPo($po);

            $needsBootstrap = $locked->status === PurchaseOrderStatus::Submitted
                && empty($locked->assigned_approver_id)
                && ! PoApprovalStep::query()->where('po_id', $locked->id)->exists();

            if ($locked->status !== PurchaseOrderStatus::Draft && ! $needsBootstrap) {
                throw new ProcurementException('Only draft purchase orders can be submitted for approval.');
            }

            $template = $this->resolveTemplate($locked);
            $fromStatus = $locked->status?->value;

            PoApprovalStep::query()->where('po_id', $locked->id)->delete();

            foreach ($template->steps as $step) {
                [$userId, $label] = $this->workflow->resolveAssignee($step, (int) $locked->created_by);

                PoApprovalStep::create([
                    'po_id'                => $locked->id,
                    'template_step_id'     => $step->id,
                    'sequence'             => $step->sequence,
                    'assignee_type'        => $step->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER,
                    'approver_user_id'     => $userId,
                    'assignee_role'        => $step->assignee_role,
                    'assignee_permission'  => $step->assignee_permission,
                    'assignee_label'       => $label,
                    'is_mandatory'         => $step->is_mandatory,
                    'status'               => PoApprovalStep::STATUS_PENDING,
                ]);
            }

            $first = PoApprovalStep::query()
                ->where('po_id', $locked->id)
                ->orderBy('sequence')
                ->first();

            if (! $first || ! $this->workflow->stepHasResolvableAssignee($first)) {
                throw new ProcurementException('Approval Template could not resolve the first approver for this PO.');
            }

            $locked->update([
                'status'               => PurchaseOrderStatus::Submitted,
                'current_sequence'     => $first->sequence,
                'assigned_approver_id' => $this->workflow->isPoolAssigneeType($first->assignee_type) ? null : $first->approver_user_id,
                'approval_template_id' => $template->id,
                'submitted_at'         => now(),
                'approved_at'          => null,
            ]);

            if ($locked->pr_id) {
                $pr = \App\Models\PurchaseRequisition::query()->whereKey($locked->pr_id)->lockForUpdate()->first();
                if ($pr) {
                    $pr->update([
                        'vendor_id' => $locked->vendor_id ?? $pr->vendor_id,
                        'status' => \App\Enums\Procurement\PurchaseRequisitionStatus::Ordered,
                        'purchase_officer_id' => $pr->purchase_officer_id ?? $actor->id,
                        'processed_at' => $pr->processed_at ?? now(),
                    ]);
                }
            }

            $this->logger->log($locked, 'po.submitted', $fromStatus, PurchaseOrderStatus::Submitted->value, $remarks, [
                'template_id'           => $template->id,
                'sequence'              => $first->sequence,
                'assigned_approver_id'  => $first->approver_user_id,
                'assignee_label'        => $first->assignee_label,
            ], $actor->id);

            return $locked->fresh(['approvalSteps.approver', 'assignedApprover']);
        });

        $this->notifyPendingPoApprovers($submitted);

        return $submitted;
    }

    // ─────────────────────────────────────────────────────────────────
    // Approve
    // ─────────────────────────────────────────────────────────────────

    public function approve(PurchaseOrder $po, User $actor, ?string $remarks = null): PurchaseOrder
    {
        $updated = DB::transaction(function () use ($po, $actor, $remarks) {
            $locked = $this->lockPo($po);
            $this->assertPending($locked);
            $this->assertActorCanAct($locked, $actor);

            $current = PoApprovalStep::query()
                ->where('po_id', $locked->id)
                ->where('sequence', (int) $locked->current_sequence)
                ->lockForUpdate()
                ->firstOrFail();

            $current->update([
                'status'   => PoApprovalStep::STATUS_APPROVED,
                'acted_by' => $actor->id,
                'remarks'  => $remarks,
                'acted_at' => now(),
            ]);

            $next = PoApprovalStep::query()
                ->where('po_id', $locked->id)
                ->where('sequence', '>', $current->sequence)
                ->where('status', PoApprovalStep::STATUS_PENDING)
                ->orderBy('sequence')
                ->first();

            while ($next && ! $next->is_mandatory && ! $this->workflow->stepHasResolvableAssignee($next)) {
                $next->update([
                    'status'   => PoApprovalStep::STATUS_SKIPPED,
                    'acted_at' => now(),
                    'remarks'  => 'Skipped: approver unavailable',
                ]);
                $next = PoApprovalStep::query()
                    ->where('po_id', $locked->id)
                    ->where('sequence', '>', $next->sequence)
                    ->where('status', PoApprovalStep::STATUS_PENDING)
                    ->orderBy('sequence')
                    ->first();
            }

            if ($next !== null) {
                if (! $this->workflow->stepHasResolvableAssignee($next)) {
                    throw new ProcurementException("Level {$next->sequence} has no resolved approver. Update the PO Approval Template.");
                }

                $locked->update([
                    'current_sequence'     => $next->sequence,
                    'assigned_approver_id' => $this->workflow->isPoolAssigneeType($next->assignee_type) ? null : $next->approver_user_id,
                ]);

                $this->logger->log($locked, 'po.approved_step', null, null, $remarks, [
                    'sequence'         => $current->sequence,
                    'next_sequence'    => $next->sequence,
                    'next_approver_id' => $next->approver_user_id,
                    'next_assignee_label' => $next->assignee_label,
                ], $actor->id);

                return $locked->fresh(['assignedApprover', 'approvalSteps.approver']);
            }

            // Final approval
            $locked->update([
                'status'               => PurchaseOrderStatus::Approved,
                'assigned_approver_id' => null,
                'current_sequence'     => null,
                'approved_at'          => now(),
            ]);

            $this->logger->log($locked, 'po.fully_approved', PurchaseOrderStatus::Submitted->value, PurchaseOrderStatus::Approved->value, $remarks, [], $actor->id);

            $this->notifications->notifyGrnTeam(
                'Purchase Order Ready for GRN',
                "PO {$locked->po_no} has been fully approved and is ready for goods receipt.",
                route('grns.create', $locked)
            );

            return $locked->fresh(['approvalSteps.approver']);
        });

        if ($updated->status === PurchaseOrderStatus::Submitted) {
            $this->notifyPendingPoApprovers($updated);
        }

        return $updated;
    }

    // ─────────────────────────────────────────────────────────────────
    // Reject
    // ─────────────────────────────────────────────────────────────────

    public function reject(PurchaseOrder $po, User $actor, ?string $remarks = null): PurchaseOrder
    {
        $updated = DB::transaction(function () use ($po, $actor, $remarks) {
            $locked = $this->lockPo($po);
            $this->assertPending($locked);
            $this->assertActorCanAct($locked, $actor);

            $current = PoApprovalStep::query()
                ->where('po_id', $locked->id)
                ->where('sequence', (int) $locked->current_sequence)
                ->lockForUpdate()
                ->first();

            if ($current) {
                $current->update([
                    'status'   => PoApprovalStep::STATUS_REJECTED,
                    'acted_by' => $actor->id,
                    'remarks'  => $remarks,
                    'acted_at' => now(),
                ]);
            }

            $locked->update([
                'status'               => PurchaseOrderStatus::Rejected,
                'assigned_approver_id' => null,
                'current_sequence'     => null,
                'remarks'              => $remarks ?: $locked->remarks,
            ]);

            $this->logger->log($locked, 'po.rejected', PurchaseOrderStatus::Submitted->value, PurchaseOrderStatus::Rejected->value, $remarks, [
                'sequence' => $current?->sequence,
            ], $actor->id);

            return $locked->fresh();
        });

        $this->notifications->notifyUsers(
            array_filter([User::find($updated->created_by)]),
            'Purchase Order Rejected',
            "{$updated->po_no} was rejected.".($remarks ? " Reason: {$remarks}" : ''),
            route('purchase-orders.show', $updated)
        );

        return $updated;
    }

    // ─────────────────────────────────────────────────────────────────
    // Send Back
    // ─────────────────────────────────────────────────────────────────

    public function sendBack(PurchaseOrder $po, User $actor, ?string $remarks = null): PurchaseOrder
    {
        $updated = DB::transaction(function () use ($po, $actor, $remarks) {
            $locked = $this->lockPo($po);
            $this->assertPending($locked);
            $this->assertActorCanAct($locked, $actor);

            $current = PoApprovalStep::query()
                ->where('po_id', $locked->id)
                ->where('sequence', (int) $locked->current_sequence)
                ->lockForUpdate()
                ->first();

            if ($current) {
                $current->update([
                    'status'   => PoApprovalStep::STATUS_SENT_BACK,
                    'acted_by' => $actor->id,
                    'remarks'  => $remarks,
                    'acted_at' => now(),
                ]);
            }

            $locked->update([
                'status'               => PurchaseOrderStatus::Draft,
                'assigned_approver_id' => null,
                'current_sequence'     => null,
                'submitted_at'         => null,
            ]);

            $this->logger->log($locked, 'po.sent_back', PurchaseOrderStatus::Submitted->value, PurchaseOrderStatus::Draft->value, $remarks, [
                'sequence' => $current?->sequence,
            ], $actor->id);

            return $locked->fresh();
        });

        $this->notifications->notifyUsers(
            array_filter([User::find($updated->created_by)]),
            'Purchase Order Sent Back',
            "{$updated->po_no} needs revision.".($remarks ? " Reason: {$remarks}" : ''),
            route('purchase-orders.show', $updated)
        );

        return $updated;
    }

    // ─────────────────────────────────────────────────────────────────
    // Timeline
    // ─────────────────────────────────────────────────────────────────

    /**
     * @return list<array{sequence: int|null, label: string, state: string, actor: ?string, remarks: ?string, acted_at: mixed}>
     */
    public function timeline(PurchaseOrder $po): array
    {
        $steps = $po->approvalSteps()->with(['approver', 'actedBy'])->orderBy('sequence')->get();

        $rows = [[
            'sequence' => null,
            'label'    => 'Created / Draft',
            'state'    => 'done',
            'actor'    => $po->createdBy?->name,
            'remarks'  => null,
            'acted_at' => $po->created_at,
        ]];

        if ($steps->isEmpty()) {
            return $rows;
        }

        foreach ($steps as $step) {
            $isCurrent = $po->status === PurchaseOrderStatus::Submitted
                && (int) $po->current_sequence === (int) $step->sequence;

            $state = match ($step->status) {
                PoApprovalStep::STATUS_APPROVED  => 'done',
                PoApprovalStep::STATUS_REJECTED  => 'rejected',
                PoApprovalStep::STATUS_SENT_BACK => 'sent_back',
                PoApprovalStep::STATUS_SKIPPED   => 'done',
                default                          => $isCurrent ? 'current' : 'pending',
            };

            $rows[] = [
                'sequence' => (int) $step->sequence,
                'label'    => 'Level '.$step->sequence.' — '.$step->displayLabel(),
                'state'    => $state,
                'actor'    => $step->actedBy?->name
                    ?? ($isCurrent && ! $this->workflow->isPoolAssigneeType($step->assignee_type)
                        ? $step->approver?->name
                        : null),
                'remarks'  => $step->remarks,
                'acted_at' => $step->acted_at,
            ];
        }

        return $rows;
    }

    public function actorCanActOnPo(PurchaseOrder $po, User $actor): bool
    {
        if ($po->status !== PurchaseOrderStatus::Submitted) {
            return false;
        }

        $current = PoApprovalStep::query()
            ->where('po_id', $po->id)
            ->where('sequence', (int) $po->current_sequence)
            ->first();

        if (! $current || $current->status !== PoApprovalStep::STATUS_PENDING) {
            return false;
        }

        try {
            $this->assertActorCanAct($po, $actor);

            return true;
        } catch (ProcurementException) {
            return false;
        }
    }

    public function currentAssigneeDisplay(PurchaseOrder $po): string
    {
        $step = $po->relationLoaded('approvalSteps')
            ? $po->approvalSteps->first(fn ($s) => (int) $s->sequence === (int) $po->current_sequence)
            : PoApprovalStep::query()
                ->where('po_id', $po->id)
                ->where('sequence', (int) $po->current_sequence)
                ->first();

        if ($step && $this->workflow->isPoolAssigneeType($step->assignee_type)) {
            return $step->displayLabel();
        }

        return $po->assignedApprover->name
            ?? $step?->displayLabel()
            ?? 'assigned approver';
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function notifyPendingPoApprovers(PurchaseOrder $po): void
    {
        $step = PoApprovalStep::query()
            ->where('po_id', $po->id)
            ->where('sequence', (int) $po->current_sequence)
            ->first();

        if (! $step) {
            return;
        }

        $recipients = $this->workflow->usersForStep($step, (int) $po->created_by);
        if ($recipients->isEmpty() && $po->assignedApprover) {
            $recipients = collect([$po->assignedApprover]);
        }

        $this->notifications->notifyUsers(
            $recipients,
            'Purchase Order Approval Pending',
            "{$po->po_no} is awaiting approval (Level {$po->current_sequence}"
                .($step->assignee_label ? ' — '.$step->assignee_label : '').').',
            route('purchase-orders.show', $po)
        );
    }

    private function assertPending(PurchaseOrder $po): void
    {
        if ($po->status !== PurchaseOrderStatus::Submitted) {
            throw new ProcurementException('This purchase order is not pending approval.');
        }
    }

    private function assertActorCanAct(PurchaseOrder $po, User $actor): void
    {
        $isPrivileged = $actor->hasAnyRole(['superadmin', 'Super Admin']);

        // Super Admin can approve, reject, or send back any pending PO.
        if ($isPrivileged) {
            return;
        }

        $current = PoApprovalStep::query()
            ->where('po_id', $po->id)
            ->where('sequence', (int) $po->current_sequence)
            ->first();

        if ($current && $current->status !== PoApprovalStep::STATUS_PENDING) {
            throw new ProcurementException('This approval level has already been completed.');
        }

        if ($current && $this->workflow->userMatchesStep($actor, $current)) {
            return;
        }

        if ((int) $po->assigned_approver_id === (int) $actor->id) {
            return;
        }

        if ((int) $po->created_by === (int) $actor->id) {
            throw new ProcurementException('You cannot approve a purchase order you created yourself.');
        }

        throw new ProcurementException(
            $current && $this->workflow->isPoolAssigneeType($current->assignee_type)
                ? 'This purchase order is awaiting '.$current->displayLabel().'. Only users with that assignment can act on it.'
                : 'This purchase order is assigned to another approver.'
        );
    }

    private function lockPo(PurchaseOrder $po): PurchaseOrder
    {
        return PurchaseOrder::query()->whereKey($po->getKey())->lockForUpdate()->firstOrFail();
    }
}
