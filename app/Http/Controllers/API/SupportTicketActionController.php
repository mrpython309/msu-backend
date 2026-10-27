<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketCommand;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Gate;
use App\Models\SupportTicketAssignment;
use App\Models\SupportTicketActivity;
use App\Models\User;
use App\AssetStatus;
use App\Models\Asset;
use App\Jobs\SendPushNotification;
use App\Helpers\Files;
use App\Services\SupportTicketCompletionFileService;

class SupportTicketActionController extends Controller
{
    use ApiResponse;

    private function isUserAdminOrAssigned(SupportTicket $ticket): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin', 'admin']) || $user->id === 1) {
            return true;
        }

        return $ticket->isAssignedTo($user);
    }
    
    public function acceptTicket(Request $request, $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!Gate::authorize('accept', $ticket)) {
            return $this->error('You are not allowed to accept this ticket.');
        }

        $assignment = SupportTicketAssignment::where('support_ticket_id', $ticket->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($assignment && $assignment->is_active) {
            return $this->error('You have already accepted this ticket.');
        }

        if (!$assignment) {
            $assignment = new SupportTicketAssignment();
            $assignment->support_ticket_id = $ticket->id;
            $assignment->user_id = auth()->user()->id;
            $assignment->assigned_by = auth()->user()->id;
            $assignment->is_active = true;
            $assignment->assigned_at = now();
        } else {
            $assignment->is_active = true;
            $assignment->assigned_at = now();
        }

        $assignment->save(); 

        $ticket->status = 'accepted';
        $ticket->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Ticket Accepted";
            $userName = auth()->user()->name;
            $message = "Your ticket #{$ticket->id} has been accepted by {$userName} and is currently being processed";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'accepted',
        ]);
        
        return $this->success('Ticket accepted successfully.');
    }

    public function completeTicket(Request $request, $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        // Normalize empty strings to null
        if ($request->input('vendor_id') === '' || $request->input('vendor_id') === 'null') {
            $request->merge(['vendor_id' => null]);
        }
        if ($request->input('total_cost') === '' || $request->input('total_cost') === 'null') {
            $request->merge(['total_cost' => null]);
        }

        $rules = SupportTicketCompletionFileService::validationRules(api: true);
        $rules['vendor_id'] = ['nullable', 'exists:vendors,id'];
        $rules['total_cost'] = ['nullable', 'numeric', 'min:0'];

        $request->validate($rules);

        if (!$this->isUserAdminOrAssigned($ticket)) {
            return $this->error('You are not allowed to complete this ticket.');
        }

        $completionFiles = app(SupportTicketCompletionFileService::class);
        $storedFiles = $completionFiles->store($completionFiles->filesFromRequest($request));

        if ($storedFiles === []) {
            return $this->error('Please attach at least one valid file.');
        }

        $ticket->status = 'completed';
        $ticket->vendor_id = $request->filled('vendor_id') ? $request->vendor_id : null;
        $ticket->total_cost = $request->filled('total_cost') ? (float) $request->total_cost : null;
        $completionFiles->applyToTicket($ticket, $storedFiles);
        $ticket->completed_ticket_commands = $request->completed_ticket_commands;

        $ticket->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Ticket Completed";
            $message = "Your ticket #{$ticket->id} has been marked as completed by the support team. Please review the resolution.";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        return $this->success('Support Ticket completed successfully.');
    }

    public function exitTicket(Request $request, $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!Gate::authorize('exit', $ticket)) {
            return $this->error('You are not allowed to exit this ticket.');
        }

        $request->validate([
            'exit_reason' => 'nullable|string|max:5000',
        ]);

        $ticket->releaseBy(auth()->user());

        if ($request->exit_reason) {
            $command = new SupportTicketCommand();
            $command->support_ticket_id = $ticket->id;
            $command->user_id = auth()->id();
            $command->ticket_response = 'Exited: ' . $request->exit_reason;
            $command->save();
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'exited',
            'meta' => $request->exit_reason ? ['reason' => $request->exit_reason] : null,
        ]);

        return $this->success('You have exited the ticket.');
    }

    public function assignTicket(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!$this->isUserAdminOrAssigned($ticket)) {
            return $this->error('You are not allowed to assign this ticket.');
        }

        $assignedUser = User::find($request->user_id);

        $assignment = new SupportTicketAssignment();
        $assignment->support_ticket_id = $ticket->id;
        $assignment->user_id = $request->user_id; 
        $assignment->assigned_by = auth()->user()->id;
        $assignment->is_active = true;
        $assignment->assigned_at = now();
        $assignment->save();    

        $assignedNames = [];
        foreach ($ticket->assignments()->where('is_active', true)->with('user')->get() as $ass) {
            if ($ass->user) {
                $assignedNames[] = $ass->user->name;
            }
        }

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Collaborator Added to Ticket";
            $message = "A collaborator has been added to ticket #{$ticket->id} They can now view updates and participate in resolving this ticket.";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'assigned',
            'meta' => [
                'assigned_to' => implode(', ', $assignedNames),
            ],
        ]);
        
        return $this->success('Support Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:work_in_progress,hold,in_purchase,completed',
        ]);

        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!$this->isUserAdminOrAssigned($ticket)) {
            return $this->error('You are not allowed to update this ticket.');
        }

        $ticket->status = $request->status;
        $ticket->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Ticket Status Updated";
            $commandedBy = auth()->user()->name;
            $message = "The status of ticket #{$ticket->id} has been updated to {$request->status} by {$commandedBy}";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }
        
        return $this->success('Support Ticket status updated successfully.');
    }

    public function rejectTicket(Request $request, $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!Gate::authorize('reject', $ticket)) {
            return $this->error('You are not allowed to reject this ticket.');
        }

        if ($ticket->status != 'completed') {
            return $this->error('Only completed tickets can be rejected.');
        }

        $request->validate([
            'reject_reason' => 'required|string|max:5000',
        ]);

        $ticket->status = 'rejected';
        $ticket->save();

        $command = new SupportTicketCommand();
        $command->support_ticket_id = $ticket->id;
        $command->user_id = auth()->id();
        $command->ticket_response = 'Rejected: ' . $request->reject_reason;
        $command->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Ticket Rejected";
            $message = "Ticket #{$ticket->id} resolution was rejected: {$request->reject_reason}";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'rejected',
            'meta' => ['reason' => $request->reject_reason],
        ]);

        return $this->success('Ticket rejected successfully.');
    }

    public function closeTicket(Request $request, $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!Gate::authorize('close', $ticket)) {
            return $this->error('You are not allowed to close this ticket.');
        }

        $ticket->status = 'closed';
        $ticket->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Ticket Closed";
            $message = "Ticket #{$ticket->id} has been successfully closed.";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'closed',
        ]);

        return $this->success('Support Ticket closed successfully.');
    }

    public function reopenTicket(Request $request, $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        if (!Gate::authorize('reopen', $ticket)) {
            return $this->error('You are not allowed to reopen this ticket.');
        }

        $ticket->status = 'reopen';
        $ticket->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "Ticket Reopened";
            $message = "Ticket #{$ticket->id} has been reopened by " . auth()->user()->name;
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'reopened',
        ]);

        return $this->success('Support Ticket reopened successfully.');
    }

    public function getAssignedPersons($id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        $assignedUsers = SupportTicketAssignment::where('support_ticket_id', $ticket->id)
            ->where('is_active', true)
            ->with(['user', 'user.designation', 'user.departments'])
            ->get();

        return $this->success($assignedUsers);
    }

    public function getActivityLogs($id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        $activities = SupportTicketActivity::where('support_ticket_id', $ticket->id)
            ->with('user')
            ->get();

        return $this->success($activities);
    }

    public function getAssetCode($id)
    {
        $asset = Asset::where('asset_code', 'like', '%'.$id.'%')->first();

        if (!$asset || $asset->status === AssetStatus::DISPOSED) {
            return $this->error('Asset not found');
        }

        return $this->success($asset);
    }

    public function getAssignableUsers($id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->error('Ticket not found');
        }

        $assignedUsers = SupportTicketAssignment::where('support_ticket_id', $ticket->id)
            ->where('is_active', true)
            ->pluck('user_id')
            ->toArray();

        $users = User::whereNotIn('id', $assignedUsers)
            ->whereHas('branches', function ($q) use ($ticket) {
                $q->where('branch_id', $ticket->branch_id);
            })
            ->whereHas('departments', function ($q) use ($ticket) {
                $q->where('department_id', $ticket->department_id);
            })
            ->get();

        return $this->success($users);
    }
}
