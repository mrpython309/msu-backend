@extends('assets.asset-layout')
 

@section('asset_section')

<div class="card card-action mb-6">
      <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Add New Warranty</h5>
        <div class="card-action-element">
          <a class="btn btn-sm btn-primary text-white" href="{{asset_show_section_url($asset, 'warranties') }}">Back to Warranties List</a>
        </div>
      </div>
      <div class="card-body">

        <form action="{{ route('asset.warranty.store') }}" method="POST">

        <div class="row g-3">

          <div class="col-4">
            <label class="form-label form-label-required" for="covered_parts">Convered Parts <span class="text-danger">*</span></label>
            <input type="text" name="covered_parts" required class="form-control @error('covered_parts') is-invalid @enderror" value="{{ old('covered_parts') }}">
            @error('covered_parts')<div class="invalid-feedback">{{ $message }}</div>@enderror
         </div>

          <div class="col-4">
            <label class="form-label form-label-required" for="warranty_type">Warrenty Type<span class="text-danger">*</span></label>
            <select id="warranty_type" name="warranty_type" class="form-select" required>
              <option value="">Select</option>
              @foreach ($warrentytypes as $warrentyType)
              <option value="{{$warrentyType->value}}"  @selected(old('warranty_number') == $warrentyType->value)>{{$warrentyType->label()}}</option>    
              @endforeach
            </select>
            @error('warranty_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-4">
            <label class="form-label">Warranty Number </label>
            <input type="text" name="warranty_number" class="form-control @error('warranty_number') is-invalid @enderror" value="{{ old('warranty_number') }}">
            @error('warranty_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-4">
            <label class="form-label" for="warranty_expire_date">Expire Date </label>
            <input type="date" name="warranty_expire_date" class="form-control @error('warranty_expire_date') is-invalid @enderror" value="{{ old('warranty_expire_date',date('Y-m-d')) }}">
            @error('warranty_expire_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

           <div class="col-4">
            <label class="form-label">Max Usage </label>           
            <div class="input-group">
                   <input type="text" name="max_usage" placeholder="Ex. 1000" value="{{old('max_usage')}}" class="form-control @error('max_usage') is-invalid @enderror">
                    <input type="text" name="usage_metric" placeholder="Ex. hours, km, days" value="{{old('usage_metric')}}" class="form-control @error('usage_metric') is-invalid @enderror">
            </div>
            @error('max_usage')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @error('usage_metric')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

         
            <div class="col-4">
            <label class="form-label" for="notes">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>

         <div class="pt-5 float-end">
            @csrf
            <input type="hidden" name="asset_id" id="asset_id" value="{{$asset->id}}">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                        <a href="{{asset_show_section_url($asset, 'warranties') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

        </form>
       
       

      </div>
    </div> 
 

 

@endsection


@section('page-script')
<script>
document.getElementById('assigned_to_type').addEventListener('change', function () {

    let type = this.value;
    let wrapper = document.getElementById('assign_target_wrapper');
    let dropdown = document.getElementById('assigned_to_id');
    let label = document.getElementById('assign_target_label');

    if (!type) {
        wrapper.classList.add('d-none');
        dropdown.innerHTML = '';
        label.innerText = 'Select';
        return;
    }

    // 🔹 Change label text
    label.innerText = type === 'branch' ? 'Select Branch ' : 'Select Asset Custodian';

    let url = this.dataset.url.replace(':type', type);

    dropdown.innerHTML = '<option value="">Loading...</option>';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            dropdown.innerHTML = '<option value="">Select</option>';

            data.forEach(item => {
                dropdown.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });

            wrapper.classList.remove('d-none');
        })
        .catch(() => {
            dropdown.innerHTML = '<option value="">Failed to load</option>';
        });
});
</script>

    
@endsection
