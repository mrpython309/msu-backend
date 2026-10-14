<?php

namespace App\Http\Requests\PaymentTerm;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PaymentTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignoreId = $this->route('paymentTerm') ?? $this->route('payment_term');

        return [
            'term_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_terms', 'term_name')->ignore($ignoreId),
            ],
            'payment_mode' => 'required|string|max:500',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('payment_terms', 'code')->ignore($ignoreId),
            ],
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        } elseif ($this->input('code') === '') {
            $this->merge(['code' => null]);
        }

        if ($this->filled('payment_mode')) {
            $this->merge(['payment_mode' => trim((string) $this->input('payment_mode'))]);
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
