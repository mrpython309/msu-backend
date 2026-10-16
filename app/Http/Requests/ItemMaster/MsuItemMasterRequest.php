<?php

namespace App\Http\Requests\ItemMaster;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class MsuItemMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_name' => [
                'required',
                'string',
                'max:1500',
                Rule::unique('msu_item_masters', 'item_name')
                    ->ignore($this->route('msuItemMaster') ?? $this->route('item_master')),
            ],
            'product_category_id' => 'required|exists:product_categories,id',
            'product_sub_category_id' => [
                'required',
                'exists:product_sub_categories,id',
                Rule::exists('product_sub_categories', 'id')->where(function ($query) {
                    $query->where('product_category_id', $this->product_category_id);
                }),
            ],
            'expense_type' => 'nullable|in:opex,capex',
            'unit_id' => 'required|exists:units,id',
            'hsn_id' => [
                'required',
                'exists:hsns,id',
            ],
            'image' => 'nullable|image|max:4096',
            'threshold_quantity' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'rates' => 'nullable|array',
            'rates.*.id' => 'nullable|integer|exists:msu_item_rates,id',
            'rates.*.rate' => 'nullable|numeric|min:0',
            'rates.*.tax' => 'nullable|numeric|min:0|max:100',
            'rates.*.effective_from' => 'nullable|required_with:rates.*.rate|date',
            'deleted_rate_ids' => 'nullable|array',
            'deleted_rate_ids.*' => 'integer|exists:msu_item_rates,id',
        ];
    }

    public function messages(): array
    {
        return [
            'item_name.unique' => 'An item with this name already exists.',
            'item_name.max' => 'Item name may not be greater than 1500 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // expense_type column remains in DB but is no longer used in the UI
        if (! $this->filled('expense_type')) {
            $this->merge(['expense_type' => 'opex']);
        }

        if ($this->has('threshold_quantity') && $this->input('threshold_quantity') === '') {
            $this->merge(['threshold_quantity' => null]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
