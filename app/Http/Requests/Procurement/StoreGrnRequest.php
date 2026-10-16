<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'Super Admin'])) {
            return true;
        }

        $allowedEmail = strtolower((string) config('procurement.grn_create_email', 'store123@gmail.com'));
        if ($allowedEmail !== '' && strtolower((string) $user->email) === $allowedEmail) {
            return true;
        }

        return $user->can('grn.create') || $user->can('grns:create');
    }

    public function rules(): array
    {
        return [
            'received_date' => 'required|date',
            'warehouse' => 'nullable|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'nullable|date',
            'bill_amount' => 'nullable|numeric|min:0',
            'bill_date' => 'nullable|date',
            'challan_number' => 'nullable|string|max:255',
            'challan_date' => 'nullable|date',
            'received_by' => 'nullable|exists:users,id',
            'received_by_phone' => 'nullable|string|min:10|max:12|regex:/^[0-9\+\-\s]+$/',
            'delivered_by' => 'required|string|max:255',
            'delivered_by_phone' => 'nullable|string|min:10|max:12|regex:/^[0-9\+\-\s]+$/',
            'vehicle_no' => 'nullable|string|max:100',
            'delivery_memo_no' => 'nullable|string|max:255',
            'delivery_memo_date' => 'nullable|date',
            'gate_entry_no' => 'nullable|string|max:255',
            'gate_entry_date' => 'nullable|date',
            'characteristic' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
            'save_as_draft' => 'nullable|boolean',
            'invoice' => 'nullable|file|max:10240',
            'delivery_challan' => 'nullable|file|max:10240',
            'photos.*' => 'nullable|image|max:10240',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:po_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
            'items.*.rejected_quantity' => 'nullable|integer|min:0',
            'items.*.free_qty' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.brand_name' => 'nullable|string|max:255',
            'items.*.model_name' => 'nullable|string|max:255',
            'items.*.serial_numbers' => 'nullable|string|max:2000',
            'items.*.remarks' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'received_date.required' => 'GRN date is required.',
            'invoice_number.required' => 'Bill number is required.',
            'received_by.required' => 'Received by person is required.',
            'delivered_by.required' => 'Delivered by person is required.',
            'received_by_phone.min' => 'Received By Ph No. must be between 10 and 12 digits.',
            'received_by_phone.max' => 'Received By Ph No. must be between 10 and 12 digits.',
            'received_by_phone.regex' => 'Received By Ph No. contains invalid characters.',
            'delivered_by_phone.min' => 'Delivered By Ph No. must be between 10 and 12 digits.',
            'delivered_by_phone.max' => 'Delivered By Ph No. must be between 10 and 12 digits.',
            'delivered_by_phone.regex' => 'Delivered By Ph No. contains invalid characters.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.po_item_id.required' => 'Purchase order item is required.',
            'items.*.received_quantity.required' => 'Received quantity is required.',
            'items.*.received_quantity.integer' => 'Received quantity must be a whole number.',
            'items.*.received_quantity.min' => 'Received quantity cannot be negative.',
        ];
    }

    public function attributes(): array
    {
        return [
            'received_date' => 'GRN date',
            'invoice_number' => 'bill number',
            'challan_number' => 'challan number',
            'items.*.received_quantity' => 'received quantity',
        ];
    }
}
