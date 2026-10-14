<?php

namespace App\Http\Requests\Unit;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('unit_name')) {
            $this->merge([
                'unit_name' => strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $this->input('unit_name')) ?? ''),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'unit_name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Z]{3,}$/',
                Rule::unique('units', 'unit_name')->ignore($this->route('unit')),
            ],
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'unit_name.min' => 'Unit must be at least 3 capital letters (e.g. KGS, NOS).',
            'unit_name.regex' => 'Unit must be capital letters only, minimum 3 (e.g. KGS, NOS).',
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
