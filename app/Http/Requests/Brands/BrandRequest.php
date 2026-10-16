<?php

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
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
          $brandId = $this->route('brand');

        // If route-model binding is used
        if (is_object($brandId)) {
            $brandId = $brandId->id;
        }

        return [
            'brand_name' => [
                'required',
                'string',
                'max:255',
                // Ignore the current brand on update
                Rule::unique('brands', 'brand_name')->ignore($brandId),
            ],
            'brand_description' => 'nullable|string',
            'status'          => 'required|in:active,inactive',
        ];
    }
}
