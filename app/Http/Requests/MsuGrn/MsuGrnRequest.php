<?php

namespace App\Http\Requests\MsuGrn;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MsuGrnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receipt_number' => 'nullable|string|max:100',
            'branch_id' => 'required|exists:branches,id',
            'msu_purchase_order_id' => 'required|exists:msu_purchase_orders,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'vendor_invoice_number' => 'required|string|max:100',
            'vendor_invoice_date' => 'required|date',
            'grn_date' => 'required|date',
            'remarks' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.msu_purchase_order_item_id' => 'required|exists:msu_purchase_order_items,id',
            'items.*.hsn' => 'nullable|string|max:50',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.is_capex' => 'nullable|boolean',
            'items.*.is_group_asset' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            $hasReceipt = false;

            foreach ($items as $row) {
                $qty = (float) ($row['received_quantity'] ?? 0);
                if ($qty > 0) {
                    $hasReceipt = true;
                }
            }

            if (! $hasReceipt) {
                $validator->errors()->add('items', 'Enter received quantity for at least one item.');
            }

            $branchId = $this->input('branch_id');
            $poId = $this->input('msu_purchase_order_id');
            if ($branchId && $poId) {
                $po = \App\Models\MsuPurchaseOrder::query()->find($poId);
                if ($po && (int) $po->branch_id !== (int) $branchId) {
                    $validator->errors()->add(
                        'msu_purchase_order_id',
                        'Selected PO does not belong to the selected branch.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Please select a branch.',
            'msu_purchase_order_id.required' => 'Please select a PO number.',
            'items.required' => 'No PO items available for receipt.',
        ];
    }
}
