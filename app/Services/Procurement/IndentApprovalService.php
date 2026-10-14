<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\IndentApprovalAction;
use App\Enums\Procurement\IndentApprovalLevel;
use App\Enums\Procurement\IndentStatus;
use App\Models\Indent;
use App\Models\IndentApproval;
use App\Models\IndentApprovalLevelConfig;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Facades\DB;

class IndentApprovalService
{
    public function __construct(
        private readonly PurchaseRequisitionService $purchaseRequisitions,
        private readonly ProcurementActivityLogger $logger,
    ) {
    }

    public function submit(Indent $indent, User $actor, ?string $comments = null): Indent
    {
        return DB::transaction(function () use ($indent, $actor, $comments) {
            $locked = $this->lockIndent($indent);

            if (! in_array($locked->status, [IndentStatus::Draft, IndentStatus::SentBack], true)) {
                throw new ProcurementException('Only draft or sent-back indents can be submitted.');
            }

            $levelsRequired = $this->resolveLevelsRequired($locked);
            $firstLevel = $this->firstActiveLevel();

            $from = $this->statusValue($locked->status);
            $to = IndentStatus::pendingForLevel($firstLevel)->value;

            $locked->update([
                'approval_levels_required' => $levelsRequired,
                'current_level' => $firstLevel,
                'status' => $to,
                'submitted_at' => $locked->submitted_at ?? now(),
                'finalized_at' => null,
            ]);

            $this->recordApproval(
                $locked,
                $actor,
                IndentApprovalLevel::fromNumber($firstLevel),
                IndentApprovalAction::Submitted,
                $from,
                $to,
                $comments
            );

            $this->logger->log($locked, 'indent.submitted', $from, $to, $comments, [
                'approval_levels_required' => $levelsRequired,
            ], $actor->id);

            return $locked->refresh();
        });
    }

    public function approve(Indent $indent, User $actor, ?string $comments = null): Indent
    {
        return DB::transaction(function () use ($indent, $actor, $comments) {
            $locked = $this->lockIndent($indent);

            if (! $locked->status instanceof IndentStatus || ! $locked->status->isPendingApproval()) {
                throw new ProcurementException('This indent cannot be approved in its current status.');
            }

            $this->assertActorCanApprove($locked, $actor);

            $currentLevel = $locked->status->level();
            $levelsRequired = max(1, (int) ($locked->approval_levels_required ?: $this->resolveLevelsRequired($locked)));
            $activeLevels = $this->activeLevelsUpTo($levelsRequired);

            if (! in_array($currentLevel, $activeLevels, true)) {
                throw new ProcurementException("Approval level [{$currentLevel}] is not active for this indent.");
            }

            $from = $locked->status->value;
            $nextLevel = $this->nextActiveLevel($currentLevel, $activeLevels);

            if ($nextLevel === null) {
                $to = IndentStatus::Approved->value;

                $locked->update([
                    'status' => $to,
                    'current_level' => $currentLevel,
                    'finalized_at' => now(),
                ]);

                $this->recordApproval(
                    $locked,
                    $actor,
                    IndentApprovalLevel::fromNumber($currentLevel),
                    IndentApprovalAction::Approved,
                    $from,
                    $to,
                    $comments
                );

                $this->logger->log($locked, 'indent.approved_final', $from, $to, $comments, [], $actor->id);

                $this->purchaseRequisitions->createFromApprovedIndent($locked);

                return $locked->refresh();
            }

            $to = IndentStatus::pendingForLevel($nextLevel)->value;

            $locked->update([
                'status' => $to,
                'current_level' => $nextLevel,
            ]);

            $this->recordApproval(
                $locked,
                $actor,
                IndentApprovalLevel::fromNumber($currentLevel),
                IndentApprovalAction::Approved,
                $from,
                $to,
                $comments
            );

            $this->logger->log($locked, 'indent.approved_level', $from, $to, $comments, [
                'level' => $currentLevel,
            ], $actor->id);

            return $locked->refresh();
        });
    }

    public function reject(Indent $indent, User $actor, ?string $comments = null): Indent
    {
        return DB::transaction(function () use ($indent, $actor, $comments) {
            $locked = $this->lockIndent($indent);

            if (! $locked->status instanceof IndentStatus || ! $locked->status->isPendingApproval()) {
                throw new ProcurementException('This indent cannot be rejected in its current status.');
            }

            $currentLevel = $locked->status->level();
            $from = $locked->status->value;
            $to = IndentStatus::Rejected->value;

            $locked->update([
                'status' => $to,
                'finalized_at' => now(),
            ]);

            $this->recordApproval(
                $locked,
                $actor,
                IndentApprovalLevel::fromNumber($currentLevel),
                IndentApprovalAction::Rejected,
                $from,
                $to,
                $comments
            );

            $this->logger->log($locked, 'indent.rejected', $from, $to, $comments, [
                'level' => $currentLevel,
            ], $actor->id);

            return $locked->refresh();
        });
    }

    public function sendBack(Indent $indent, User $actor, ?string $comments = null): Indent
    {
        return DB::transaction(function () use ($indent, $actor, $comments) {
            $locked = $this->lockIndent($indent);

            if (! $locked->status instanceof IndentStatus || ! $locked->status->isPendingApproval()) {
                throw new ProcurementException('This indent cannot be sent back in its current status.');
            }

            $currentLevel = $locked->status->level();
            $from = $locked->status->value;
            $to = IndentStatus::SentBack->value;

            $locked->update([
                'status' => $to,
                'current_level' => null,
            ]);

            $this->recordApproval(
                $locked,
                $actor,
                IndentApprovalLevel::fromNumber($currentLevel),
                IndentApprovalAction::SentBack,
                $from,
                $to,
                $comments
            );

            $this->logger->log($locked, 'indent.sent_back', $from, $to, $comments, [
                'level' => $currentLevel,
            ], $actor->id);

            return $locked->refresh();
        });
    }

    public function ensurePurchaseRequisition(Indent $indent): ?PurchaseRequisition
    {
        if (! in_array($indent->status, [IndentStatus::Approved, IndentStatus::ConvertedToPurchaseRequisition], true)) {
            return null;
        }

        return $this->purchaseRequisitions->createFromApprovedIndent($indent);
    }

    private function lockIndent(Indent $indent): Indent
    {
        return Indent::query()->whereKey($indent->getKey())->lockForUpdate()->firstOrFail();
    }

    /**
     * Approval hygiene: the requester cannot approve their own indent, and each
     * level (L1/L2/L3) must be approved by a different person.
     */
    private function assertActorCanApprove(Indent $indent, User $actor): void
    {
        if (config('procurement.approvals.prevent_self_approval', true)
            && (int) $indent->requested_by === (int) $actor->id
            && ! $actor->hasAnyRole(['superadmin', 'Super Admin'])
        ) {
            throw new ProcurementException('You cannot approve an indent you requested yourself.');
        }

        if (config('procurement.approvals.require_distinct_approvers', true)) {
            $alreadyApproved = $indent->approvals()
                ->where('approver_id', $actor->id)
                ->where('action', IndentApprovalAction::Approved->value)
                ->exists();

            if ($alreadyApproved) {
                throw new ProcurementException('Each approval level (L1, L2, L3) must be approved by a different person. You have already approved this indent at an earlier level.');
            }
        }
    }

    private function resolveLevelsRequired(Indent $indent): int
    {
        $configured = IndentApprovalLevelConfig::activeLevelCount();
        $default = (int) config('procurement.default_approval_levels', 3);

        $levels = $indent->approval_levels_required ?: ($configured > 0 ? $configured : $default);

        return max(1, min(3, (int) $levels));
    }

    private function firstActiveLevel(): int
    {
        $levels = IndentApprovalLevelConfig::activeLevels();

        if ($levels === []) {
            return 1;
        }

        return (int) $levels[0];
    }

    /**
     * @return list<int>
     */
    private function activeLevelsUpTo(int $maxLevel): array
    {
        $levels = IndentApprovalLevelConfig::activeLevels();

        if ($levels === []) {
            return range(1, $maxLevel);
        }

        return array_values(array_filter($levels, fn (int $level) => $level <= $maxLevel));
    }

    /**
     * @param  list<int>  $activeLevels
     */
    private function nextActiveLevel(int $currentLevel, array $activeLevels): ?int
    {
        foreach ($activeLevels as $level) {
            if ($level > $currentLevel) {
                return $level;
            }
        }

        return null;
    }

    private function recordApproval(
        Indent $indent,
        User $actor,
        IndentApprovalLevel $level,
        IndentApprovalAction $action,
        ?string $fromStatus,
        string $toStatus,
        ?string $comments,
    ): IndentApproval {
        return $indent->approvals()->create([
            'approver_id' => $actor->id,
            'level' => $level,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'comments' => $comments,
            'acted_at' => now(),
            'meta' => [
                'approval_levels_required' => $indent->approval_levels_required,
                'current_level' => $indent->current_level,
            ],
        ]);
    }

    private function statusValue(mixed $status): ?string
    {
        if ($status instanceof IndentStatus) {
            return $status->value;
        }

        return $status !== null ? (string) $status : null;
    }
}
