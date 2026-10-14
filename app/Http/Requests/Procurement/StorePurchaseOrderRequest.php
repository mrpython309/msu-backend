<?php

namespace App\Http\Requests\Procurement;

use App\Enums\Procurement\PurchaseOrderSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (
            $user->hasAnyRole(['superadmin', 'Super Admin'])
            || $user->can('purchase-order.create')
            || $user->can('purchase-orders:create')
        );
    }

    public function prepareForValidation(): void
    {
        $items = $this->input('items', []);

        if (is_array($items)) {
            foreach ($items as $i => $item) {
                if (empty($item['item_name']) && ! empty($item['item_code'])) {
                    $items[$i]['item_name'] = $item['item_code'];
                }
                if (empty($item['unit']) && ! empty($item['purchase_unit'])) {
                    $items[$i]['unit'] = $item['purchase_unit'];
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        $source = $this->input('po_source');
        $fromPr = in_array($source, [
            PurchaseOrderSource::FromPurchaseRequisition->value,
            PurchaseOrderSource::FromIndent->value,
        ], true);

        return [
            'po_source' => ['required', Rule::in(array_column(PurchaseOrderSource::cases(), 'value'))],
            'action' => ['required', Rule::in(['draft', 'submit'])],
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'purchase_date' => ['nullable', 'date'],
            'delivery_date' => ['required', 'date'],
            'po_type' => ['required', 'string', 'max:50'],
            'grn_store' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'store_id' => ['nullable', 'integer', 'exists:departments,id'],
            'department_name' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'string', 'max:20'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'minimum_advance_payment_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'quotation_id' => ['nullable', 'integer', 'exists:vendor_quotations,id'],
            'pr_id' => [$fromPr ? 'required' : 'nullable', 'integer', 'exists:purchase_requisitions,id'],
            'indent_id' => ['nullable', 'integer', 'exists:indents,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.indent_item_id' => ['nullable', 'integer', 'exists:indent_items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_code' => ['nullable', 'string', 'max:255'],
            'items.*.hsn_code' => ['nullable', 'string', 'max:50'],
            'items.*.manufacturer' => ['nullable', 'string', 'max:255'],
            'items.*.marketer_name' => ['nullable', 'string', 'max:255'],
            'items.*.item_type' => ['nullable', 'string', 'max:50'],
            'items.*.item_category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'items.*.item_sub_category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'items.*.purchase_unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.mrp' => ['nullable', 'numeric', 'min:0'],
            'items.*.gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.epr' => ['nullable', 'numeric', 'min:0'],
            'items.*.free_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.has_free_qty' => ['nullable', 'boolean'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'max:20'],
            'items.*.conversion' => ['nullable', 'string', 'max:100'],
            'items.*.qoh' => ['nullable', 'numeric', 'min:0'],
            'items.*.estimated_delivery_date' => ['nullable', 'date'],
            'items.*.remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'vendor_id' => 'supplier name',
            'delivery_date' => 'estimated delivery date',
            'grn_store' => 'GRN department',
            'department_id' => 'department name',
            'po_type' => 'purchase order type',
            'pr_id' => 'purchase requisition',
            'items.*.price' => 'purchase rate',
            'items.*.quantity' => 'purchase quantity',
            'items.*.item_name' => 'asset name',
            'items.*.item_code' => 'asset code',
            'items.*.item_category_id' => 'asset category',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            if (is_array($items)) {
                foreach ($items as $index => $item) {
                    $qty = (int) ($item['quantity'] ?? 0);
                    $freeQty = !empty($item['has_free_qty']) ? (float) ($item['free_qty'] ?? 0) : 0;
                    
                    if ($qty <= 0 && $freeQty <= 0) {
                        $validator->errors()->add("items.{$index}.quantity", "Either Purchase Quantity or Free Quantity must be greater than 0.");
                    }
                }
            }
        });
    }
}
