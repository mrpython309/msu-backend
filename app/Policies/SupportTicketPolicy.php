<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupportTicketPolicy
{

    public function accept(User $user, SupportTicket $ticket): bool
    {
        // Admins can accept any open ticket
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $ticket->status === 'open' && !$ticket->assignments()->where('is_active', 1)->exists();
        }

        return
            $ticket->status === 'open'
            && ! $ticket->assignments()
                ->where('is_active', 1)
                ->exists()
            && $user->branches()
                ->where('branches.id', $ticket->branch_id)
                ->exists()
            && $user->departments()
                ->where('departments.id', $ticket->department_id)
                ->exists();
    }

    public function exit(User $user, SupportTicket $ticket): bool
    {
        return $ticket->isAssignedTo($user)
            && in_array($ticket->status, [
                'accepted',
                'work_in_progress',
                'hold',
                'in_purchase',
                'reopen',
                'rejected'
            ]);
    }

    public function close(User $user, SupportTicket $ticket): bool
    {
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $ticket->status === 'completed';
        }

        return
            $ticket->status === 'completed'
            && $ticket->created_by === $user->id;
    }

    public function reject(User $user, SupportTicket $ticket): bool
    {
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $ticket->status === 'completed';
        }

        return
            $ticket->status === 'completed'
            && $ticket->created_by === $user->id;
    }

    public function reopen(User $user, SupportTicket $ticket): bool
    {
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $ticket->status === 'closed';
        }

        return
            $ticket->status === 'closed'
            && $ticket->created_by === $user->id;
    }

    public function update(User $user, SupportTicket $ticket): bool
    {

         if($ticket->status != 'open'){
            return false;
        }

        // Super Admin can edit anything
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return true;
        }       

        // Creator can edit
        return $ticket->created_by === $user->id;
    }

    public function delete(User $user, SupportTicket $ticket): bool
    {
        if($ticket->status != 'open'){
            return false;
        }

        // Super Admin can delete anything
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return true;
        }         

        // Creator can delete
        return $ticket->created_by === $user->id;
    }

    
}
