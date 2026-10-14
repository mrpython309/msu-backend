<?php

namespace App\Policies;

use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssetAssignmentPolicy
{

    public function respond(User $user, AssetAssignment $assignment): bool
    {
        // Superadmin / Admin can do anything
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin', 'admin'])) {
            return true;
        }

        // Only TO-BRANCH users can accept/reject/return
        return $user->branches()
            ->where('branches.id', $assignment->to_branch)
            ->exists();
    }


    public function editOrDelete(User $user, AssetAssignment $assignment): bool
    {

        if($assignment->transfer_status!='in_transit'){
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin', 'admin'])) {
            return true;
        }

        return $user->hasBranch($assignment->from_branch);
 
    }


    public function updateStatus(
        User $user,
        AssetAssignment $assignment,
        string $action
    ): bool {

        
        // Action → required current status
        $rules = [
            'accept'        => [AssetAssignment::IN_TRANSIT],
            'reject'        => [AssetAssignment::IN_TRANSIT],
            'return'        => [AssetAssignment::ACCEPTED],
            'accept_return' => [AssetAssignment::RETURN,AssetAssignment::REJECTED,],
        ];

        if (!isset($rules[$action])) {
            return false;
        }

        // Status check
        if (!in_array($assignment->transfer_status, $rules[$action], true)) {
            return false;
        }

         /*
        |--------------------------------------------------------------------------
        | Superadmin override (AFTER status check)
        |--------------------------------------------------------------------------
        */
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin', 'admin'])) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | TO (branch OR employee)
        |--------------------------------------------------------------------------
        */
        if (in_array($action, ['accept', 'reject', 'return'], true)) {

            if ($assignment->isToEmployee()) {
                if ((int) $assignment->assiged_emoloyee === (int) $user->id) {
                    return true;
                }

                return $assignment->to_branch
                    ? $user->hasBranch($assignment->to_branch)
                    : false;
            }

            if ($assignment->isToBranch()) {
                return $user->hasBranch($assignment->to_branch);
            }

            // Destination branch set even when assigned_to is null/legacy
            if ($assignment->to_branch) {
                return $user->hasBranch($assignment->to_branch);
            }

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | FROM (branch only)
        |--------------------------------------------------------------------------
        */
        if ($action === 'accept_return') {
            return $user->hasBranch($assignment->from_branch);
        }

        return false;
    }
    
}
