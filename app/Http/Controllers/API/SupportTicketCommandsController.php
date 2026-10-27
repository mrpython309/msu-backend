<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicketCommand;
use App\Traits\ApiResponse;
use App\Models\SupportTicket;
use App\Models\SupportTicketActivity;
use App\Helpers\Files;
use App\Services\SupportTicketReplyFileService;
use Illuminate\Support\Str;
use App\Jobs\SendPushNotification;

class SupportTicketCommandsController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ticketId = $request->ticket_id ?? $request->support_ticket_id;
        if (!$ticketId) {
            return $this->error('Ticket Id Not Found');
        }

        $commands = SupportTicketCommand::where('support_ticket_id', $ticketId)
            ->with(['user', 'user.designation', 'user.departments'])
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->success($commands);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ticketId = $request->ticket_id ?? $request->support_ticket_id;
        if ($ticketId) {
            $request->merge(['ticket_id' => $ticketId, 'support_ticket_id' => $ticketId]);
        }

        $request->validate(array_merge([
            'ticket_response' => 'nullable|string',
            'voice_note' => 'nullable|file|mimes:webm,mp3,wav,ogg|max:5120',
            'recording_duration' => 'nullable|integer|min:1|max:300',
            'ticket_id' => 'required|exists:support_tickets,id',
        ], SupportTicketReplyFileService::attachmentValidationRules(api: true)));

        $replyFiles = app(SupportTicketReplyFileService::class);
        $uploadedFiles = $replyFiles->filesFromRequest($request);

        if (empty($request->ticket_response) && ! $request->hasFile('voice_note') && $uploadedFiles === []) {
            return $this->error('Please enter a response, attach a file, or upload a voice note.');
        }

        $ticket = SupportTicket::find($ticketId);

        if (!$ticket) {
            return $this->error('Support Ticket not found');
        }

        if ($ticket->status == 'closed') {
            return $this->error('Support Ticket is closed');
        }

        $command = new SupportTicketCommand();
        $command->support_ticket_id = $ticket->id;
        $command->user_id = auth()->user()->id;
        $command->ticket_response = $request->ticket_response;
        $command->recording_duration = $request->recording_duration;

        if ($uploadedFiles !== []) {
            $replyFiles->applyToCommand($command, $replyFiles->store($uploadedFiles));
        }

        if ($request->hasFile('voice_note') && $request->file('voice_note')->isValid()) {
            $command->voice_note = Files::uploadLocalOrS3($request->voice_note, SupportTicketCommand::FILE_PATH);
        }

        $command->save();

        $ticketTokens = $ticket->getAssignedAndCreatorDeviceTokens();

        if (!empty($ticketTokens)) {
            $title = "New Comment on Ticket";
            $commandedBy = auth()->user()->name;
            $message = "A new comment has been added to ticket #{$ticket->id} by {$commandedBy}. Please review the latest update.";
            $data = [
                'type' => 'support_ticket',
                'ref_id' => (string)$ticket->id
            ];
            SendPushNotification::dispatch($ticketTokens, $title, $message, $data);
        }

        SupportTicketActivity::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'event' => 'commented',
            'meta' => [
                'message' => Str::limit($request->ticket_response, 100),
            ],
        ]);

        return $this->success($command);
    }
}
