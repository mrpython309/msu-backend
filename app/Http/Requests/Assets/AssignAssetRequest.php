<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $custodianId = $this->input('assiged_emoloyee');

        $this->merge([
            'assigned_to' => $custodianId ? 'employee' : 'branch',
            'assiged_emoloyee' => $custodianId ?: null,
            'to_location' => $this->input('to_location') ?: null,
            'department_id' => $this->input('department_id') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
           'asset_id' => ['required', 'exists:assets,id'],

            'assign_date' => ['required', 'date'],

            'assigned_to' => ['required', Rule::in(['branch', 'employee'])],

            'to_company_id' => ['required', 'exists:companies,id'],
            'from_company_id'=> ['nullable', 'exists:companies,id'],
            'from_branch' => ['nullable', 'exists:branches,id'],
            'from_location' => ['nullable', 'exists:branch_locations,id'],

            'to_branch' => [
                'required',
                'exists:branches,id',
            ],

            'to_location' => [
                'nullable',
                'exists:branch_locations,id',
                'different:from_location',
            ],

            'assiged_emoloyee' => [
                'nullable',
                'exists:asset_custodians,id',
            ],

            'department_id' => ['nullable', 'exists:departments,id'],

            'notes' => ['nullable', 'string'],
        ];
    }
}
