<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProductSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_category_id' => 'required|exists:product_categories,id',
            'sub_category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_sub_categories', 'sub_category_name')
                    ->where('product_category_id', $this->product_category_id)
                    ->ignore($this->route('productSubCategory') ?? $this->route('product_sub_category')),
            ],
            'status' => 'required|in:active,inactive',
        ];
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
