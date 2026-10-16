<?php

namespace App\Http\Requests\MiscellaneousCharge;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class MiscellaneousChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignoreId = $this->route('miscellaneousCharge') ?? $this->route('miscellaneous_charge');

        return [
            'charge_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('miscellaneous_charges', 'charge_name')->ignore($ignoreId),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('miscellaneous_charges', 'code')->ignore($ignoreId),
            ],
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }

        if (! $this->filled('amount')) {
            $this->merge(['amount' => 0]);
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
