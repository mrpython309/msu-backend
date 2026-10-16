<?php

namespace App\Http\Requests\Hsn;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class HsnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('hsn_code')) {
            $this->merge([
                'hsn_code' => strtoupper(trim((string) $this->input('hsn_code'))),
            ]);
        }

        if (! $this->filled('tax_rate')) {
            $this->merge(['tax_rate' => 0]);
        }
    }

    public function rules(): array
    {
        $ignoreId = $this->route('hsn');

        return [
            'hsn_code' => [
                'required',
                'string',
                'size:4',
                Rule::unique('hsns', 'hsn_code')->ignore($ignoreId),
            ],
            'description' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
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
