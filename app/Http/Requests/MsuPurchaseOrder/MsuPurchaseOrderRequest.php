<?php

namespace App\Http\Requests\MsuPurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class MsuPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_date' => 'required|date|before_or_equal:today',
            'delivery_date' => 'nullable|date',
            'date_of_commencement' => 'nullable|date',
            'vendor_id' => 'required|exists:vendors,id',
            'branch_id' => 'required|exists:branches,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'department_id' => 'nullable|exists:departments,id',
            'shipping_detail_id' => 'nullable|exists:shipping_details,id',
            'remarks' => 'nullable|string|max:5000',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'charges' => 'nullable|array',
            'charges.*.charge_name' => 'nullable|string|max:255|distinct',
            'charges.*.amount' => 'nullable|numeric|min:0',
            'charges.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.msu_item_master_id' => 'required|distinct|exists:msu_item_masters,id',
            'items.*.hsn' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one item to the purchase order.',
            'items.*.msu_item_master_id.required' => 'Each line must have an item selected.',
            'items.*.msu_item_master_id.distinct' => 'The same item cannot be added more than once.',
            'charges.*.charge_name.distinct' => 'The same miscellaneous charge cannot be added more than once.',
            'branch_id.required' => 'Please select a branch. The PO number is generated from the branch.',
        ];
    }
}
