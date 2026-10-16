<?php

namespace App\Http\Requests\ProductCategory;

use App\Models\ProductCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $submitted = strtoupper(trim((string) $this->input('category_code', '')));
        $fromInput = preg_replace('/[^A-Z]/', '', $submitted) ?: '';

        // Prefer the editable Short Name; fall back to auto-generate from category name.
        $code = $fromInput !== ''
            ? substr($fromInput, 0, 3)
            : ProductCategory::codeFromName($this->input('category_name'));

        $this->merge([
            'category_code' => $code,
        ]);
    }

    public function rules(): array
    {
        $ignoreId = $this->route('productCategory') ?? $this->route('product_category');

        return [
            'category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'category_name')->ignore($ignoreId),
            ],
            'category_code' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('product_categories', 'category_code')->ignore($ignoreId),
            ],
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'category_code.size' => 'Short Name must be exactly 3 letters (A–Z).',
            'category_code.regex' => 'Short Name must be exactly 3 letters (A–Z).',
            'category_code.unique' => 'Another category already uses this Short Name. Choose a different code.',
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
