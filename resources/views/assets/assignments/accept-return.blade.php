@extends('assets.asset-layout')
 

@section('asset_section')



<div class="card card-action mb-6">
      <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0"> Assignment - Accept Return </h5>
        <div class="card-action-element">
          <a class="btn btn-sm btn-primary text-white" href="{{asset_show_section_url($asset, 'assignments') }}">Back to Assigned List</a>
        </div>
      </div>
      <div class="card-body">

        <form action="{{ route('asset.assignment.return.accept.update',$assignment) }}" method="POST">

        <div class="row g-3">

           <div class="col-4">
           <div class="d-flex align-items-center gap-4 me-5">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-store icon-lg"></i></div>
            </div>
            <div>
                  <span>From Branch</span>
              <h5 class="mb-0">{{$assignment->fromBranch->name ?? '--'}}</h5>
            
            </div>
          </div>
           </div>

           <div class="col-4">
           <div class="d-flex align-items-center gap-4 me-5">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-map icon-lg"></i></div>
            </div>
            <div>
             <span>From Location</span>
             <h5 class="mb-0">{{$assignment->fromBranchLocation->location_name ?? '--'}}</h5>
            
            </div>
          </div>
           </div>

            <div class="col-4">
           <div class="d-flex align-items-center gap-4 me-5">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-store icon-lg"></i></div>
            </div>
            <div>
                 <span>To Branch</span>
              <h5 class="mb-0">{{$assignment->toBranch->name ?? '--'}}</h5>
             
            </div>
          </div>
           </div>

           <div class="col-4">
           <div class="d-flex align-items-center gap-4 me-5">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-map icon-lg"></i></div>
            </div>
            <div>
             <span>To Location</span>
             <h5 class="mb-0">{{$assignment->toBranchLocation->location_name ?? '--'}}</h5>
            
            </div>
          </div>
           </div>

         
 

            <div class="col-4">
            <label class="form-label" for="return_accepted_notes">Return Accept Notes <span class="text-danger">*</span></label>
            <textarea class="form-control @error('return_notes') is-invalid @enderror" id="return_accepted_notes" name="return_accepted_notes" rows="2" required>{{ old('return_notes') }}</textarea>
            @error('return_accepted_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>

         <div class="pt-5 float-end">
            @csrf
           @method('PUT')
                        <button type="submit" class="btn btn-primary">
                            Accept Return
                        </button>
                        <a href="{{asset_show_section_url($asset, 'assignments') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

        </form>
       
       

      </div>
    </div> 

    

    
 

 

@endsection


@section('page-script')

<script>

$(document).ready(function () {

    $('#to_branch').on('change', function () {

        let branchId = $(this).val();
        let locationSelect = $('#to_location');

        locationSelect.html('<option value="">Loading...</option>');

        if (!branchId) {
            locationSelect.html('<option value="">Select Physical Location</option>');
            return;
        }

        $.ajax({
            url: "{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId),
            type: "GET",
            dataType: "json",
            success: function (res) {

               let locationSelect = $('#to_location');
                locationSelect.empty().append('<option value="">Select Physical Location</option>');

                if (res.status && res.count > 0) {
                    $.each(res.data, function (i, item) {
                        locationSelect.append(
                            `<option value="${item.id}">${item.location_name}</option>`
                        );
                    });
                } else {
                    locationSelect.append('<option value="">No locations available</option>');
                }
            },
            error: function () {
                alert('Failed to load locations. Please try again.');
                locationSelect.html('<option value="">Select Physical Location</option>');
            }
        });
    });

});

</script>

@endsection
