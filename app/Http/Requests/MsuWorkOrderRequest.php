<?php

namespace App\Http\Requests;

use App\Enums\Procurement\MsuWorkOrderFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MsuWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wo_date' => 'required|date',
            'contract_period_from' => 'nullable|date',
            'contract_period_to' => 'nullable|date',
            'frequency' => ['nullable', Rule::enum(MsuWorkOrderFrequency::class)],
            'subject' => 'nullable|string',
            'vendor_id' => 'required|exists:vendors,id',
            'branch_id' => 'required|exists:branches,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'department_id' => 'nullable|exists:departments,id',
            'shipping_detail_id' => 'nullable|exists:shipping_details,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'miscellaneous_charge_id' => 'nullable|exists:miscellaneous_charges,id',
            'remarks' => 'nullable|string',

            // Work Details array
            'items' => 'required|array|min:1',
            'items.*.work_description' => 'required|string|max:1000',
            'items.*.scope_of_work' => 'required|string|max:2000',
            'items.*.hsn' => 'nullable|string|max:50',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax' => 'required|numeric|min:0|max:100',
            
            // Charges array
            'charges' => 'nullable|array',
            'charges.*.charge_name' => 'nullable|string|max:255',
            'charges.*.amount' => 'nullable|numeric|min:0',
            'charges.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one work detail is required.',
            'items.*.work_description.required' => 'Work description is required.',
            'items.*.scope_of_work.required' => 'Scope of work is required.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
