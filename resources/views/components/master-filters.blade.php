@props([
    'action',
    'placeholder' => 'Search',
    'showStatus' => true,
])

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ $action }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="master-search">Search</label>
                <input type="text" id="master-search" name="search" class="form-control" placeholder="{{ $placeholder }}" value="{{ request('search') }}">
            </div>
            {{ $slot }}
            @if($showStatus)
            <div class="col-md-2">
                <label class="form-label" for="master-status">Status</label>
                <select id="master-status" name="status" class="form-select select2">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label" for="master-per-page">Per Page</label>
                <select id="master-per-page" name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((string) request('per_page', 10) === (string) $size)>{{ $size }}</option>
                    @endforeach
                    <option value="all" @selected(request('per_page') === 'all' || (int) request('per_page') === 1000000)>All</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ $action }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>
