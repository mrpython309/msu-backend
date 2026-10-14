<div class="col-md-3">
    <label class="form-label" for="collaborator_id">Collaborators</label>
    <select class="form-select {{ !empty($collaboratorSelect2) ? 'select2' : '' }}" name="collaborator_id" id="collaborator_id">
        <option value="">All Collaborators</option>
        @foreach(($collaboratorUsers ?? collect()) as $collaboratorUser)
            <option value="{{ $collaboratorUser->id }}" {{ (string) request('collaborator_id') === (string) $collaboratorUser->id ? 'selected' : '' }}>
                {{ $collaboratorUser->name }}
            </option>
        @endforeach
    </select>
</div>
