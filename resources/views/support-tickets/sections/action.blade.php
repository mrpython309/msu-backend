   <!-- Action Card -->
      @canany(['accept', 'exit', 'close','reopen', 'reject'], $supportTicket)
       <div class="card mb-6">
        <div class="card-body">

          @can('accept', $supportTicket)
          <a href="{{ route('support-ticket.accept',$supportTicket->id) }}" class="btn btn-primary w-100 mb-3">Accept</a>
          @endcan

          @can('exit', $supportTicket)

            <form method="POST" action="{{route('support-ticket.exit', $supportTicket->id)}}" class="mt-4 exit-ticket-from">

                @csrf
                <button type="submit" class="btn btn-danger d-grid w-100">
                    <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                class="icon-base bx bx-paper-plane icon-sm me-2"></i>Exit Ticket</span>
                </button>


            </form>

            <hr>

              <form method="POST" action="{{route('support-ticket.status.update', $supportTicket->id)}}">
              <div>
              <label for="user_id" class="form-label">Status</label>
              <select class="form-select mb-6" id="status" name="status" required>
                <option value="">Select Status</option>
              {{-- <option value="accepted" {{ $supportTicket->status == 'accepted' ? 'selected' : '' }}>Accepted</option> --}}
              <option value="work_in_progress" {{ $supportTicket->status == 'work_in_progress' ? 'selected' : '' }}>Work In Progress</option>
              <option value="hold" {{ $supportTicket->status == 'hold' ? 'selected' : '' }}>Hold</option>
              <option value="in_purchase" {{ $supportTicket->status == 'in_purchase' ? 'selected' : '' }}>In Purchase</option>
            {{-- @if($supportTicket->status == 'reopen')  <option value="reopen" {{ $supportTicket->status == 'reopen' ? 'selected' : '' }}>Reopen</option> @endif --}}
              </select> 
              </div>
              @method('PUT')
              @csrf
              <button type="submit" class="btn btn-success d-grid w-100">
                <span class="d-flex align-items-center justify-content-center text-nowrap"> Update Status</span>
              </button>
            </form>          

            <hr>


             
            <button type="button" class="btn btn-success d-grid w-100 mb-3" data-bs-toggle="modal" data-bs-target="#makeascompletemodel">
              <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                class="icon-base bx bx-paper-plane icon-sm me-2"></i>Mark as Complete</span>
            </button>

            @include('support-tickets.model.mark-as-complete')

         
 
          

          @endcan


          

            

            @can('close', $supportTicket)


            <ul class="list-unstyled mb-6">
               @if($supportTicket->vendor)
              <li class="mb-2">
                <span class="h6">Vendor :</span>
                <span>{{$supportTicket->vendor->name ?? '-'}}</span>
              </li>
              @endif
               @if($supportTicket->total_cost)
              <li class="mb-2">
                <span class="h6">Total Cost :</span>
                <span>{{ $supportTicket->total_cost ?? '-' }}</span>
              </li>
              @endif
              @if($supportTicket->completed_ticket_commands)
              <li class="mb-2">
                <span class="h6">Commands :</span>
                <span>{{ $supportTicket->completed_ticket_commands ?? '-' }}</span>
              </li>
              @endif
                @if($supportTicket->completed_filenames !== [])
              <li class="mb-2">
                <span class="h6 mb-3 d-block">Files on Completion :</span>
                <span>@include('support-tickets.partials.completed-files')</span>
              </li>
              @endif
              
            </ul>



              <form method="POST" action="{{route('support-ticket.rejectticket', $supportTicket->id)}}" class="mt-4 reject-ticket-form">
                @csrf

                <button type="submit" class="btn btn-warning d-grid w-100 mb-3">
                    <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                class="icon-base bx bx-x icon-sm me-2"></i>Reject Ticket</span>
                </button>

              </form>

              <form method="POST" action="{{route('support-ticket.closeticket', $supportTicket->id)}}" class="mt-4 close-ticket-form">
                @csrf

                <button type="submit" class="btn btn-danger d-grid w-100">
                    <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                class="icon-base bx bx-paper-plane icon-sm me-2"></i>Close Ticket</span>
                </button>

              </form>

            
             @endcan    
             
             @can('reopen', $supportTicket)

              <form method="POST" action="{{route('support-ticket.reopenticket', $supportTicket->id)}}" class="mt-4 reopen-ticket-form">
                @csrf

                <button type="submit" class="btn btn-warning d-grid w-100">
                    <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                class="icon-base bx bx-history icon-sm me-2"></i>Reopen Ticket</span>
                </button>

              </form>

            
             @endcan   
             
             
             

        </div>
       </div>
       @endcan