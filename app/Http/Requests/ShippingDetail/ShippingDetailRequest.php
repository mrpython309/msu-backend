<?php

namespace App\Http\Requests\ShippingDetail;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ShippingDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignoreId = $this->route('shippingDetail') ?? $this->route('shipping_detail');

        return [
            'shipping_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shipping_details', 'shipping_name')->ignore($ignoreId),
            ],
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:2000',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
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
