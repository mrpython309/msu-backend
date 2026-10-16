<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\IndentStatus;
use App\Models\ApprovalHistory;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateStep;
use App\Models\AssetCategory;
use App\Models\Indent;
use App\Models\IndentApprovalStep;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Category-driven Approval Template engine.
 * Steps may assign a specific user, a Spatie role, or a permission.
 * User steps pin one approver; role/permission steps are a pool — any matching user can act.
 */
class ApprovalWorkflowService
{
    public function __construct(
        private readonly PurchaseRequisitionService $purchaseRequisitions,
        private readonly ProcurementActivityLogger $logger,
        private readonly ProcurementNotificationService $notifications,
    ) {
    }

    public function resolveTemplate(Indent $indent): ApprovalTemplate
    {
        $categoryId = $indent->workflowCategoryId();
        $categoryName = $indent->itemCategory?->category_name
            ?? ($categoryId ? AssetCategory::query()->find($categoryId)?->category_name : null)
            ?? 'this item category';

        $template = ApprovalTemplate::forCategory($categoryId);

        if (! $template || $template->steps->isEmpty()) {
            throw new ProcurementException(
                'No active Approval Template is configured for '.$categoryName.'. '
                .'Ask an administrator to create one under Setting → Approval Templates.'
            );
        }

        if (! $template->isFullyConfigured()) {
            throw new ProcurementException(
                'The approval template "'.$template->name.'" is incomplete for '.$categoryName.'. '
                .'Assign an approver for every level under Setting → Approval Templates.'
            );
        }

        $template->loadMissing(['steps.approver']);

        return $template;
    }

    /**
     * @return Collection<int, IndentApprovalStep>
     */
    public function stepsForIndent(Indent $indent): Collection
    {
        return $indent->approvalSteps()->with(['approver', 'actedBy'])->orderBy('sequence')->get();
    }

    /**
     * Refresh pending indent steps from the linked approval template
     * (e.g. after template edits or fixing legacy fallback assignees).
     */
    public function syncPendingStepsFromTemplate(Indent $indent): Indent
    {
        if (! $indent->statusEnum()?->isPendingApproval() || ! $indent->approval_template_id) {
            return $indent;
        }

        $template = ApprovalTemplate::query()
            ->with(['steps.approver'])
            ->find($indent->approval_template_id);

        if (! $template) {
            return $indent;
        }

        DB::transaction(function () use ($indent, $template) {
            foreach ($template->steps as $templateStep) {
                $indentStep = IndentApprovalStep::query()
                    ->where('indent_id', $indent->id)
                    ->where('sequence', $templateStep->sequence)
                    ->first();

                if (! $indentStep || $indentStep->status !== IndentApprovalStep::STATUS_PENDING) {
                    continue;
                }

                [$userId, $label] = $this->resolveAssignee($templateStep, (int) $indent->requested_by);

                $indentStep->update([
                    'template_step_id' => $templateStep->id,
                    'assignee_type' => $templateStep->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER,
                    'approver_user_id' => $userId,
                    'assignee_role' => $templateStep->assignee_role,
                    'assignee_permission' => $templateStep->assignee_permission,
                    'assignee_label' => $label,
                    'is_mandatory' => $templateStep->is_mandatory,
                ]);
            }

            $current = IndentApprovalStep::query()
                ->where('indent_id', $indent->id)
                ->where('sequence', (int) $indent->current_sequence)
                ->first();

            if ($current) {
                $indent->update([
                    'assigned_approver_id' => $this->isPoolAssigneeType($current->assignee_type)
                        ? null
                        : $current->approver_user_id,
                    'assigned_permission' => $current->assignee_permission,
                ]);
            }
        });

        $indent->refresh();
        $indent->load(['approvalSteps.approver', 'approvalSteps.actedBy', 'assignedApprover', 'approvalTemplate']);

        return $indent;
    }

    public function submit(Indent $indent, User $actor, ?string $remarks = null): Indent
    {
        $submitted = DB::transaction(function () use ($indent, $actor, $remarks) {
            $locked = $this->lockIndent($indent);

            if (! in_array($locked->status, [IndentStatus::Draft, IndentStatus::SentBack], true)) {
                throw new ProcurementException('Only draft or sent-back indents can be submitted.');
            }

            $template = $this->resolveTemplate($locked);
            $fromStatus = $locked->status->value;

            IndentApprovalStep::query()->where('indent_id', $locked->id)->delete();

            foreach ($template->steps as $step) {
                [$userId, $label] = $this->resolveAssignee($step, (int) $locked->requested_by);

                IndentApprovalStep::create([
                    'indent_id' => $locked->id,
                    'template_step_id' => $step->id,
                    'sequence' => $step->sequence,
                    'assignee_type' => $step->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER,
                    'approver_user_id' => $userId,
                    'assignee_role' => $step->assignee_role,
                    'assignee_permission' => $step->assignee_permission,
                    'assignee_label' => $label,
                    'is_mandatory' => $step->is_mandatory,
                    'status' => IndentApprovalStep::STATUS_PENDING,
                ]);
            }

            $first = IndentApprovalStep::query()
                ->where('indent_id', $locked->id)
                ->orderBy('sequence')
                ->first();

            if (! $first || ! $this->stepHasResolvableAssignee($first)) {
                throw new ProcurementException('Approval Template could not resolve the first approver.');
            }

            $now = now();

            $locked->update([
                'status' => IndentStatus::PendingApproval,
                'approval_status' => 'Pending',
                'approval_template_id' => $template->id,
                'current_sequence' => $first->sequence,
                'current_level' => $first->sequence,
                'assigned_approver_id' => $this->isPoolAssigneeType($first->assignee_type) ? null : $first->approver_user_id,
                'assigned_permission' => $first->assignee_permission,
                'approved_at' => null,
                'submitted_at' => $now,
                'requested_at' => $locked->requested_at ?? $now,
                'finalized_at' => null,
            ]);

            ApprovalHistory::create([
                'indent_id' => $locked->id,
                'workflow_id' => null,
                'sequence' => null,
                'approver_id' => $actor->id,
                'action' => ApprovalHistory::ACTION_SUBMITTED,
                'remarks' => $remarks,
                'action_date' => $now,
            ]);

            $this->logger->log($locked, 'indent.submitted', $fromStatus, IndentStatus::PendingApproval->value, $remarks, [
                'template_id' => $template->id,
                'sequence' => $first->sequence,
                'assigned_approver_id' => $first->approver_user_id,
                'assignee_label' => $first->assignee_label,
            ], $actor->id);

            return $locked->fresh(['approvalSteps.approver', 'assignedApprover']);
        });

        $this->notifyPendingIndentApprovers($submitted);

        $this->notifications->notifyDepartment(
            $submitted,
            $submitted->department_id,
            'Indent Submitted',
            "{$submitted->indent_no} was submitted for approval.",
            route('indents.show', $submitted)
        );

        return $submitted;
    }

    public function approve(Indent $indent, User $actor, ?string $remarks = null): Indent
    {
        [$updated, $isFinal, $pr] = DB::transaction(function () use ($indent, $actor, $remarks) {
            $locked = $this->lockIndent($indent);
            $this->assertPending($locked);
            $this->assertActorCanAct($locked, $actor);

            $current = IndentApprovalStep::query()
                ->where('indent_id', $locked->id)
                ->where('sequence', (int) $locked->current_sequence)
                ->lockForUpdate()
                ->firstOrFail();

            $current->update([
                'status' => IndentApprovalStep::STATUS_APPROVED,
                'acted_by' => $actor->id,
                'remarks' => $remarks,
                'acted_at' => now(),
            ]);

            ApprovalHistory::create([
                'indent_id' => $locked->id,
                'workflow_id' => null,
                'sequence' => $current->sequence,
                'approver_id' => $actor->id,
                'action' => ApprovalHistory::ACTION_APPROVED,
                'remarks' => $remarks,
                'action_date' => now(),
            ]);

            $next = IndentApprovalStep::query()
                ->where('indent_id', $locked->id)
                ->where('sequence', '>', $current->sequence)
                ->where('status', IndentApprovalStep::STATUS_PENDING)
                ->orderBy('sequence')
                ->first();

            while ($next && ! $next->is_mandatory && ! $this->stepHasResolvableAssignee($next)) {
                $next->update([
                    'status' => IndentApprovalStep::STATUS_SKIPPED,
                    'acted_at' => now(),
                    'remarks' => 'Skipped: approver unavailable',
                ]);
                $next = IndentApprovalStep::query()
                    ->where('indent_id', $locked->id)
                    ->where('sequence', '>', $next->sequence)
                    ->where('status', IndentApprovalStep::STATUS_PENDING)
                    ->orderBy('sequence')
                    ->first();
            }

            if ($next !== null) {
                if (! $this->stepHasResolvableAssignee($next)) {
                    throw new ProcurementException(
                        "Level {$next->sequence} has no resolved approver. Update the Approval Template."
                    );
                }

                $locked->update([
                    'current_sequence' => $next->sequence,
                    'current_level' => $next->sequence,
                    'assigned_approver_id' => $this->isPoolAssigneeType($next->assignee_type) ? null : $next->approver_user_id,
                    'assigned_permission' => $next->assignee_permission,
                ]);

                $this->logger->log($locked, 'indent.approved_step', null, null, $remarks, [
                    'sequence' => $current->sequence,
                    'next_sequence' => $next->sequence,
                    'next_approver_id' => $next->approver_user_id,
                    'next_assignee_label' => $next->assignee_label,
                ], $actor->id);

                return [$locked->fresh(['assignedApprover', 'approvalSteps.approver']), false, null];
            }

            $locked->update([
                'status' => IndentStatus::Approved,
                'approval_status' => 'Approved',
                'assigned_approver_id' => null,
                'assigned_permission' => null,
                'current_sequence' => null,
                'approved_at' => now(),
                'finalized_at' => now(),
            ]);

            $this->logger->log($locked, 'indent.fully_approved', IndentStatus::PendingApproval->value, IndentStatus::Approved->value, $remarks, [], $actor->id);

            $pr = $this->purchaseRequisitions->createFromApprovedIndent($locked);

            // After PR generation, mark as Completed for the business status set.
            $locked->refresh();
            if ($locked->status === IndentStatus::ConvertedToPurchaseRequisition
                || $locked->status === IndentStatus::Approved) {
                $locked->update(['status' => IndentStatus::Completed]);
            }

            return [$locked->fresh(['approvalSteps.approver']), true, $pr];
        });

        if ($isFinal) {
            $this->notifications->notifyUsers(
                array_filter([$updated->requestedBy]),
                'Indent Fully Approved',
                "{$updated->indent_no} has been fully approved.",
                route('indents.show', $updated)
            );
            // Purchase Officers are notified by PurchaseRequisitionService on PR create.
        } else {
            $this->notifyPendingIndentApprovers($updated);
        }

        return $updated;
    }

    public function reject(Indent $indent, User $actor, ?string $remarks = null): Indent
    {
        $updated = DB::transaction(function () use ($indent, $actor, $remarks) {
            $locked = $this->lockIndent($indent);
            $this->assertPending($locked);
            $this->assertActorCanAct($locked, $actor);

            $current = IndentApprovalStep::query()
                ->where('indent_id', $locked->id)
                ->where('sequence', (int) $locked->current_sequence)
                ->lockForUpdate()
                ->first();

            if ($current) {
                $current->update([
                    'status' => IndentApprovalStep::STATUS_REJECTED,
                    'acted_by' => $actor->id,
                    'remarks' => $remarks,
                    'acted_at' => now(),
                ]);
            }

            $locked->update([
                'status' => IndentStatus::Rejected,
                'approval_status' => 'Rejected',
                'assigned_approver_id' => null,
                'finalized_at' => now(),
            ]);

            ApprovalHistory::create([
                'indent_id' => $locked->id,
                'sequence' => $current?->sequence,
                'approver_id' => $actor->id,
                'action' => ApprovalHistory::ACTION_REJECTED,
                'remarks' => $remarks,
                'action_date' => now(),
            ]);

            $this->logger->log($locked, 'indent.rejected', IndentStatus::PendingApproval->value, IndentStatus::Rejected->value, $remarks, [
                'sequence' => $current?->sequence,
            ], $actor->id);

            return $locked->fresh();
        });

        $this->notifications->notifyUsers(
            array_filter([$updated->requestedBy]),
            'Indent Rejected',
            "{$updated->indent_no} was rejected.".($remarks ? " Reason: {$remarks}" : ''),
            route('indents.show', $updated)
        );

        return $updated;
    }

    public function sendBack(Indent $indent, User $actor, ?string $remarks = null): Indent
    {
        $updated = DB::transaction(function () use ($indent, $actor, $remarks) {
            $locked = $this->lockIndent($indent);
            $this->assertPending($locked);
            $this->assertActorCanAct($locked, $actor);

            $current = IndentApprovalStep::query()
                ->where('indent_id', $locked->id)
                ->where('sequence', (int) $locked->current_sequence)
                ->lockForUpdate()
                ->first();

            if ($current) {
                $current->update([
                    'status' => IndentApprovalStep::STATUS_SENT_BACK,
                    'acted_by' => $actor->id,
                    'remarks' => $remarks,
                    'acted_at' => now(),
                ]);
            }

            $locked->update([
                'status' => IndentStatus::SentBack,
                'approval_status' => 'Sent Back',
                'current_sequence' => null,
                'current_level' => null,
                'assigned_approver_id' => null,
                'assigned_permission' => null,
            ]);

            ApprovalHistory::create([
                'indent_id' => $locked->id,
                'sequence' => $current?->sequence,
                'approver_id' => $actor->id,
                'action' => ApprovalHistory::ACTION_SENT_BACK,
                'remarks' => $remarks,
                'action_date' => now(),
            ]);

            $this->logger->log($locked, 'indent.sent_back', IndentStatus::PendingApproval->value, IndentStatus::SentBack->value, $remarks, [
                'sequence' => $current?->sequence,
            ], $actor->id);

            return $locked->fresh();
        });

        $this->notifications->notifyUsers(
            array_filter([$updated->requestedBy]),
            'Indent Sent Back',
            "{$updated->indent_no} needs revision.".($remarks ? " Reason: {$remarks}" : ''),
            route('indents.edit', $updated)
        );

        return $updated;
    }

    /**
     * @return list<array{sequence: int|null, label: string, permission: ?string, state: string, actor: ?string, remarks: ?string, acted_at: mixed}>
     */
    public function timeline(Indent $indent): array
    {
        $steps = $this->stepsForIndent($indent);
        $submitted = $indent->approvalHistories
            ->where('action', ApprovalHistory::ACTION_SUBMITTED)
            ->sortByDesc('id')
            ->first();

        $rows = [[
            'sequence' => null,
            'label' => 'Submitted',
            'permission' => null,
            'state' => $submitted || $indent->status !== IndentStatus::Draft ? 'done' : 'pending',
            'actor' => $submitted?->approver?->name ?? $indent->requestedBy?->name,
            'remarks' => $submitted?->remarks,
            'acted_at' => $submitted?->action_date,
        ]];

        if ($steps->isEmpty()) {
            return $rows;
        }

        foreach ($steps as $step) {
            $state = match ($step->status) {
                IndentApprovalStep::STATUS_APPROVED => 'done',
                IndentApprovalStep::STATUS_REJECTED => 'rejected',
                IndentApprovalStep::STATUS_SENT_BACK => 'sent_back',
                IndentApprovalStep::STATUS_SKIPPED => 'done',
                default => (
                    $indent->status?->isPendingApproval()
                    && (int) $indent->current_sequence === (int) $step->sequence
                ) ? 'current' : 'pending',
            };

            $rows[] = [
                'sequence' => (int) $step->sequence,
                'label' => $step->displayLabel(),
                'permission' => $this->isPoolAssigneeType($step->assignee_type)
                    ? ($step->assignee_permission ?: null)
                    : null,
                'state' => $state,
                'actor' => $step->actedBy?->name
                    ?? ($state === 'current' && ! $this->isPoolAssigneeType($step->assignee_type)
                        ? ($step->approver?->name ?? $step->displayLabel())
                        : ($state === 'current' ? $step->displayLabel() : null)),
                'remarks' => $step->remarks,
                'acted_at' => $step->acted_at,
            ];
        }

        return $rows;
    }

    /**
     * Resolve assignee for a template step using the configured template values only.
     * User steps pin the exact person selected in the Approval Template.
     * Role/permission steps are a pool (no single user id).
     *
     * @return array{0: ?int, 1: string} [userId|null, label]
     */
    public function resolveAssignee(ApprovalTemplateStep $step, int $requesterId): array
    {
        $type = $step->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER;

        if ($type === ApprovalTemplateStep::ASSIGNEE_USER) {
            if (! $step->approver_user_id) {
                throw new ProcurementException("Level {$step->sequence}: no approver user configured.");
            }

            $user = $step->relationLoaded('approver')
                ? $step->approver
                : User::query()->with('roles')->find($step->approver_user_id);

            if (! $user) {
                throw new ProcurementException("Level {$step->sequence}: configured approver user no longer exists.");
            }

            return [(int) $step->approver_user_id, $step->assigneeLabel()];
        }

        if ($type === ApprovalTemplateStep::ASSIGNEE_ROLE) {
            $role = trim((string) $step->assignee_role);
            if ($role === '') {
                throw new ProcurementException("Level {$step->sequence}: no role configured.");
            }

            $users = $this->usersWithRole($role, $requesterId);

            // Role is Admin/Super Admin and only the requester holds it — allow pool (self via privileged).
            if ($users->isEmpty() && $this->isPrivilegedApproverRole($role)) {
                $self = User::query()->find($requesterId);
                if ($self && $this->userMaySelfApprove($self) && $self->hasAnyRole($this->privilegedRoleNames())) {
                    return [null, $role];
                }
            }

            if ($users->isEmpty()) {
                throw new ProcurementException(
                    "Level {$step->sequence}: no active user found with role [{$role}] (excluding requester)."
                );
            }

            // Pool: show role name only — any user with this role can approve.
            return [null, $role];
        }

        if ($type === ApprovalTemplateStep::ASSIGNEE_PERMISSION) {
            $permission = trim((string) $step->assignee_permission);
            if ($permission === '') {
                throw new ProcurementException("Level {$step->sequence}: no permission configured.");
            }

            $users = User::permission($permission)
                ->where('id', '!=', $requesterId)
                ->orderBy('id')
                ->get();

            if ($users->isEmpty()) {
                $self = User::query()->find($requesterId);
                if ($self && $this->userMaySelfApprove($self) && $self->can($permission)) {
                    return [null, $permission];
                }

                throw new ProcurementException(
                    "Level {$step->sequence}: no active user found with permission [{$permission}] (excluding requester)."
                );
            }

            return [null, $permission];
        }

        throw new ProcurementException("Level {$step->sequence}: unknown assignee type [{$type}].");
    }

    public function isPoolAssigneeType(?string $type): bool
    {
        return in_array($type, [
            ApprovalTemplateStep::ASSIGNEE_ROLE,
            ApprovalTemplateStep::ASSIGNEE_PERMISSION,
        ], true);
    }

    public function stepHasResolvableAssignee(IndentApprovalStep|\App\Models\PoApprovalStep $step): bool
    {
        $type = $step->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER;

        return match ($type) {
            ApprovalTemplateStep::ASSIGNEE_ROLE => trim((string) $step->assignee_role) !== '',
            ApprovalTemplateStep::ASSIGNEE_PERMISSION => trim((string) $step->assignee_permission) !== '',
            default => ! empty($step->approver_user_id),
        };
    }

    public function userMatchesStep(User $user, IndentApprovalStep|\App\Models\PoApprovalStep $step): bool
    {
        $type = $step->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER;

        return match ($type) {
            ApprovalTemplateStep::ASSIGNEE_ROLE => $this->userHasRoleName($user, (string) $step->assignee_role),
            ApprovalTemplateStep::ASSIGNEE_PERMISSION => $user->can((string) $step->assignee_permission),
            default => (int) $step->approver_user_id === (int) $user->id,
        };
    }

    public function actorCanActOnIndent(Indent $indent, User $actor): bool
    {
        if (! $indent->statusEnum()?->isPendingApproval()) {
            return false;
        }

        try {
            $this->assertActorCanAct($indent, $actor);

            return true;
        } catch (ProcurementException) {
            return false;
        }
    }

    public function userIsCurrentAssignee(Indent $indent, User $user): bool
    {
        $step = $indent->relationLoaded('approvalSteps')
            ? $indent->approvalSteps->first(fn ($s) => (int) $s->sequence === (int) $indent->current_sequence)
            : IndentApprovalStep::query()
                ->where('indent_id', $indent->id)
                ->where('sequence', (int) $indent->current_sequence)
                ->first();

        if ($step) {
            return $this->userMatchesStep($user, $step);
        }

        return (int) $indent->assigned_approver_id === (int) $user->id;
    }

    public function currentAssigneeDisplay(Indent $indent): string
    {
        $step = $indent->approvalSteps
            ->first(fn ($s) => (int) $s->sequence === (int) $indent->current_sequence)
            ?? $indent->approvalSteps()->where('sequence', (int) $indent->current_sequence)->first();

        if ($step && $this->isPoolAssigneeType($step->assignee_type)) {
            return $step->displayLabel();
        }

        return $indent->assignedApprover->name
            ?? $step?->displayLabel()
            ?? 'assigned approver';
    }

    /**
     * @return Collection<int, User>
     */
    public function usersForStep(IndentApprovalStep|\App\Models\PoApprovalStep $step, ?int $excludeUserId = null): Collection
    {
        $type = $step->assignee_type ?: ApprovalTemplateStep::ASSIGNEE_USER;

        if ($type === ApprovalTemplateStep::ASSIGNEE_ROLE) {
            return $this->usersWithRole((string) $step->assignee_role, $excludeUserId);
        }

        if ($type === ApprovalTemplateStep::ASSIGNEE_PERMISSION) {
            $query = User::permission((string) $step->assignee_permission)->orderBy('id');
            if ($excludeUserId) {
                $query->where('id', '!=', $excludeUserId);
            }

            return $query->get();
        }

        if ($step->approver_user_id) {
            $user = User::query()->find($step->approver_user_id);

            return $user ? collect([$user]) : collect();
        }

        return collect();
    }

    private function notifyPendingIndentApprovers(Indent $indent): void
    {
        $step = IndentApprovalStep::query()
            ->where('indent_id', $indent->id)
            ->where('sequence', (int) $indent->current_sequence)
            ->first();

        if (! $step) {
            return;
        }

        $recipients = $this->usersForStep($step, (int) $indent->requested_by);
        if ($recipients->isEmpty() && $indent->assignedApprover) {
            $recipients = collect([$indent->assignedApprover]);
        }

        $this->notifications->notifyUsers(
            $recipients,
            'Indent Approval Pending',
            "{$indent->indent_no} is awaiting approval (Level {$indent->current_sequence}"
                .($step->assignee_label ? ' — '.$step->assignee_label : '').').',
            route('indents.show', $indent)
        );
    }

    /**
     * @return Collection<int, User>
     */
    private function usersWithRole(string $role, ?int $excludeUserId = null): Collection
    {
        $role = trim($role);
        if ($role === '') {
            return collect();
        }

        try {
            $query = User::role($role)->orderBy('id');
            if ($excludeUserId) {
                $query->where('id', '!=', $excludeUserId);
            }
            $users = $query->get();
            if ($users->isNotEmpty()) {
                return $users;
            }
        } catch (\Throwable) {
            // try case-insensitive below
        }

        $matchedRole = \Spatie\Permission\Models\Role::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($role)])
            ->where('guard_name', 'web')
            ->value('name');

        if (! $matchedRole) {
            return collect();
        }

        try {
            $query = User::role($matchedRole)->orderBy('id');
            if ($excludeUserId) {
                $query->where('id', '!=', $excludeUserId);
            }

            return $query->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function userHasRoleName(User $user, string $role): bool
    {
        $role = trim($role);
        if ($role === '') {
            return false;
        }

        if ($user->hasRole($role)) {
            return true;
        }

        return $user->roles->contains(
            fn ($r) => mb_strtolower((string) $r->name) === mb_strtolower($role)
        );
    }

    /**
     * @return list<string>
     */
    private function privilegedRoleNames(): array
    {
        return ['Admin', 'admin', 'Super Admin', 'superadmin'];
    }

    private function userMaySelfApprove(User $user): bool
    {
        return $user->hasAnyRole($this->privilegedRoleNames());
    }

    private function isPrivilegedApproverRole(string $role): bool
    {
        return in_array(mb_strtolower(trim($role)), ['admin', 'super admin', 'superadmin'], true);
    }

    private function assertPending(Indent $indent): void
    {
        if (! $indent->statusEnum()?->isPendingApproval()) {
            throw new ProcurementException('This indent is not pending approval.');
        }
    }

    private function assertActorCanAct(Indent $indent, User $actor): void
    {
        $current = IndentApprovalStep::query()
            ->where('indent_id', $indent->id)
            ->where('sequence', (int) $indent->current_sequence)
            ->first();

        if ($current && $this->userMatchesStep($actor, $current)) {
            return;
        }

        if ((int) $indent->assigned_approver_id === (int) $actor->id) {
            return;
        }

        if ((int) $indent->requested_by === (int) $actor->id) {
            throw new ProcurementException('You cannot act on an indent you requested yourself.');
        }

        throw new ProcurementException(
            $current && $this->isPoolAssigneeType($current->assignee_type)
                ? 'This indent is awaiting '.$current->displayLabel().'. Only users with that assignment can act on it.'
                : 'This indent is assigned to another approver. Only the assigned user can act on it.'
        );
    }

    private function lockIndent(Indent $indent): Indent
    {
        return Indent::query()->whereKey($indent->getKey())->lockForUpdate()->firstOrFail();
    }
}
