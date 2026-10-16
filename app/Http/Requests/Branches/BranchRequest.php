<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class BranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

         $branchId = $this->route('branch');

        // If route-model binding is used
        if (is_object($branchId)) {
            $branchId = $branchId->id;
        }

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                // Unique in dealer_locations table, ignore current branch on update
                Rule::unique('branches', 'code')->ignore($branchId),
            ],
            'company_id' => 'required|exists:companies,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gst_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }
}
