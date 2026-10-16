<?php

namespace App\Services\Procurement;

use App\Models\Grn;
use App\Models\User;
use App\Notifications\ProcurementNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class ProcurementNotificationService
{
    public function notifyUsers(iterable $users, string $title, string $message, ?string $url = null): void
    {
        $recipients = collect($users)->filter()->unique('id');
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ProcurementNotification($title, $message, $url));
        }
    }

    public function notifyDepartment(Model $record, int $departmentId, string $title, string $message, ?string $url = null): void
    {
        $users = User::query()->whereHas('departments', fn ($q) => $q->whereKey($departmentId))->get();
        $this->notifyUsers($users, $title, $message, $url);
    }

    public function notifyApprovers(string $title, string $message, ?string $url = null, string $permission = 'indents:approve'): void
    {
        $this->notifyUsers(
            $this->usersWithAnyPermission([$permission]),
            $title,
            $message,
            $url
        );
    }

    public function notifyPurchaseTeam(string $title, string $message, ?string $url = null): void
    {
        $this->notifyPurchaseOfficers($title, $message, $url);
    }

    /**
     * Notify every user with the Purchase Officer role (or process permission fallback).
     * Never throws — missing roles/permissions must not block GRN/PO flows.
     */
    public function notifyPurchaseOfficers(string $title, string $message, ?string $url = null): void
    {
        try {
            $this->ensureRoleExists('Purchase Officer');

            $byRole = $this->usersWithRole('Purchase Officer');

            $byPermission = $this->usersWithAnyPermission([
                'purchase-requisition.process',
                'purchase-requisition.view',
            ]);

            $this->notifyUsers($byRole->merge($byPermission)->unique('id'), $title, $message, $url);
        } catch (Throwable $e) {
            Log::warning('notifyPurchaseOfficers failed: '.$e->getMessage());
        }
    }

    /**
     * Notify store / GRN users when a PO is ready for goods receipt.
     */
    public function notifyGrnTeam(string $title, string $message, ?string $url = null): void
    {
        try {
            $this->notifyUsers(
                $this->usersWithAnyPermission(['grn.create', 'grns:create', 'grn.view', 'grns:view']),
                $title,
                $message,
                $url
            );
        } catch (Throwable $e) {
            Log::warning('notifyGrnTeam failed: '.$e->getMessage());
        }
    }

    public function notifyGrnCreated(Grn $grn, User $creator): void
    {
        $url = route('grns.show', $grn);
        $message = "GRN {$grn->grn_no} has been created for PO {$grn->purchaseOrder?->po_no}.";

        $this->notifyPurchaseOfficers('GRN Successfully Created', $message, $url);
        $this->notifyGrnTeam('GRN Successfully Created', $message, $url);
    }

    public function notifyGrnApproved(Grn $grn, User $approver, int $assetsCreated = 0): void
    {
        $url = route('grns.show', $grn);
        $poNo = $grn->purchaseOrder?->po_no ?? 'N/A';

        if ($assetsCreated > 0) {
            try {
                $this->notifyUsers(
                    $this->usersWithAnyPermission(['asset:view', 'asset:create', 'dashboard.asset:view']),
                    'New Assets Created',
                    "{$assetsCreated} asset(s) created from GRN {$grn->grn_no} (PO {$poNo}).",
                    $url
                );
            } catch (Throwable $e) {
                Log::warning('notifyGrnApproved asset notify failed: '.$e->getMessage());
            }
        }

        $requesterId = $grn->purchaseOrder?->purchaseRequisition?->indent?->requested_by;
        if ($requesterId) {
            $requester = User::query()->find($requesterId);
            if ($requester) {
                $this->notifyUsers(
                    collect([$requester]),
                    'Items Successfully Received',
                    "Goods for your indent have been received under GRN {$grn->grn_no}.",
                    $url
                );
            }
        }

        $this->notifyPurchaseOfficers(
            'GRN Approved',
            "GRN {$grn->grn_no} has been approved by {$approver->name}.",
            $url
        );
    }

    private function ensureRoleExists(string $roleName, string $guard = 'web'): void
    {
        Role::findOrCreate($roleName, $guard);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Resolve users for a role without using Spatie's throwing User::role() scope.
     *
     * @return Collection<int, User>
     */
    private function usersWithRole(string $roleName, string $guard = 'web'): Collection
    {
        $roleId = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', $guard)
            ->value('id');

        if (! $roleId) {
            return collect();
        }

        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('roles.id', $roleId))
            ->get();
    }

    /**
     * Resolve users for any of the given permissions that actually exist.
     * Avoids Spatie PermissionDoesNotExist when a name is mistyped/missing.
     *
     * @param  list<string>  $permissions
     * @return Collection<int, User>
     */
    private function usersWithAnyPermission(array $permissions): Collection
    {
        $existingIds = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissions)
            ->pluck('id');

        if ($existingIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->where(function ($q) use ($existingIds) {
                $q->whereHas('permissions', fn ($p) => $p->whereIn('permissions.id', $existingIds))
                    ->orWhereHas('roles.permissions', fn ($p) => $p->whereIn('permissions.id', $existingIds));
            })
            ->get();
    }
}
