@extends('layouts/contentNavbarLayout')

@section('title', 'Approval Templates')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Approval Templates</h4>
    </div>
    @canany(['approval-templates:create', 'approval-templates:manage'])
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-template-modal">
        <i class="bx bx-plus me-1"></i> New Template
    </button>
    @endcanany
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible">{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@forelse ($templates as $template)
    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">{{ $template->name }}</h5>
            </div>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-label-{{ $template->is_active ? 'success' : 'secondary' }} align-self-center">
                    {{ $template->is_active ? 'Active' : 'Disabled' }}
                </span>
                @unless ($template->isFullyConfigured())
                    <span class="badge bg-label-danger align-self-center">Incomplete</span>
                @endunless
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                        data-bs-target="#edit-template-modal-{{ $template->id }}">
                    <i class="bx bx-edit me-1"></i> Edit
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#add-step-modal-{{ $template->id }}">
                    Add Level
                </button>
                <form method="POST" action="{{ route('approval-templates.toggle', $template) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-warning" type="submit">
                        {{ $template->is_active ? 'Disable' : 'Enable' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('approval-templates.destroy', $template) }}"
                      onsubmit="return confirm('Delete this template and all its levels?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width:100px">Level</th>
                        <th>Assignee</th>
                        <th style="width:120px">Mandatory</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($template->steps as $step)
                        <tr>
                            <td><span class="badge bg-label-secondary">{{ $step->sequence }}</span></td>
                            <td>
                                <span class="badge bg-label-primary me-1">{{ strtoupper($step->assignee_type ?: 'user') }}</span>
                                {{ $step->assigneeLabel() }}
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $step->is_mandatory ? 'info' : 'warning' }}">
                                    {{ $step->is_mandatory ? 'Yes' : 'Optional' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#edit-step-modal-{{ $step->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('approval-templates.steps.destroy', $step) }}"
                                          onsubmit="return confirm('Remove this level?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No levels yet. Click <strong>Add Level</strong> to configure the approval chain.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Edit template modal (outside table) --}}
    <div class="modal fade" id="edit-template-modal-{{ $template->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('approval-templates.update', $template) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Module</label>
                        <input type="text" class="form-control" disabled
                               value="{{ $template->module === 'po' ? 'Purchase Order' : 'Indent / Asset Requisition' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" required value="{{ old('name', $template->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Default (fallback for categories without a template)</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $template->category_id) === (string) $category->id)>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Template</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add level modal --}}
    <div class="modal fade" id="add-step-modal-{{ $template->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('approval-templates.steps.store', $template) }}" class="modal-content assignee-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Level — {{ $template->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('settings.approval-templates._assignee-fields', [
                        'prefix' => 'add-'.$template->id,
                        'step' => null,
                        'users' => $users,
                        'roles' => $roles,
                        'permissions' => $permissions,
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Add Level</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit level modals (must be outside <table>) --}}
    @foreach ($template->steps as $step)
        <div class="modal fade" id="edit-step-modal-{{ $step->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('approval-templates.steps.update', $step) }}" class="modal-content assignee-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Level {{ $step->sequence }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('settings.approval-templates._assignee-fields', [
                            'prefix' => 'edit-'.$step->id,
                            'step' => $step,
                            'users' => $users,
                            'roles' => $roles,
                            'permissions' => $permissions,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save Level</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@empty
    <div class="card">
        <div class="card-body text-center py-5">
            <h5>No approval templates yet</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-template-modal">Create Template</button>
        </div>
    </div>
@endforelse

<div class="modal fade" id="add-template-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('approval-templates.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">New Approval Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Module <span class="text-danger">*</span></label>
                    <select name="module" class="form-select" id="new-template-module">
                        <option value="">Indent / Asset Requisition</option>
                        <option value="po">Purchase Order</option>
                    </select>
                    <small class="text-muted">
                        Indent and PO templates are separate — same category can have different approvers for each.
                    </small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                    <input name="name" class="form-control" required placeholder="e.g. CCTV PO Approval" value="{{ old('name') }}">
                </div>
                <div class="mb-3" id="new-template-category-wrap">
                    <label class="form-label">Item Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Default (fallback for categories without a template)</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted" id="new-template-category-hint">
                        Pick CCTV, Furniture, etc. Leave Default for a fallback workflow.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function syncAssigneePanels(form) {
        const typeSelect = form.querySelector('[name="assignee_type"]');
        if (!typeSelect) return;

        const type = typeSelect.value;
        form.querySelectorAll('[data-assignee-panel]').forEach(function (panel) {
            const show = panel.getAttribute('data-assignee-panel') === type;
            panel.classList.toggle('d-none', !show);
            panel.querySelectorAll('select, input').forEach(function (el) {
                el.disabled = !show;
                if (el.tagName === 'SELECT' && el.name !== 'assignee_type') {
                    if (show) {
                        el.setAttribute('required', 'required');
                    } else {
                        el.removeAttribute('required');
                    }
                }
            });
        });
    }

    document.querySelectorAll('.assignee-form').forEach(function (form) {
        const typeSelect = form.querySelector('[name="assignee_type"]');
        if (!typeSelect) return;

        typeSelect.addEventListener('change', function () {
            syncAssigneePanels(form);
        });
        syncAssigneePanels(form);
    });

    // Re-sync when a modal opens so disabled fields match the selected type
    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const form = modal.querySelector('.assignee-form');
            if (form) syncAssigneePanels(form);
        });
    });
});
</script>
@endsection
