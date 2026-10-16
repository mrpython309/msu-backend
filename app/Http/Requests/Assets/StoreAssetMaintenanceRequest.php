<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetMaintenanceRequest extends FormRequest
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
         return [
            'asset_id'          => ['required', 'exists:assets,id'],
            'maintenance_date'  => ['required', 'date'],
            'reported_by'       => ['nullable', 'exists:users,id'],
            'vendor_id'         => ['required', 'exists:vendors,id'],
            'priority'          => ['required', 'in:low,medium,high,critical'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'maintenance_cost'  => ['nullable', 'numeric', 'min:0'],
            'start_date'        => ['nullable', 'date'],
            'end_date'          => ['nullable', 'date', 'after_or_equal:start_date'],
            'maintenance_notes' => ['nullable', 'string'],
            'remarks'           => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'maintenance_date.required' => 'Maintenance date is required.',
            'reported_by.required'      => 'Please select who reported this maintenance.',
            'vendor_id.required'        => 'Vendor selection is required.',
            'priority.required'         => 'Priority is required.',
            'end_date.after_or_equal'   => 'End date must be after or equal to start date.',
        ];
    }
}
