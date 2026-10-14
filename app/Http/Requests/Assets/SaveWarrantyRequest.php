<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;
use App\WarrantyType;
use Illuminate\Validation\Rules\Enum;


class SaveWarrantyRequest extends FormRequest
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
        return [
            'asset_id' => ['required', 'exists:assets,id'],

            'covered_parts' => ['required', 'string', 'max:255'],

            'warranty_type' => [
                'required',
                new Enum(WarrantyType::class),
            ],

            'warranty_number' => ['nullable', 'string', 'max:100'],

            'warranty_expire_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'max_usage' => ['nullable', 'integer', 'min:1'],

            'usage_metric' => ['nullable', 'string', 'max:50'],

            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'covered_parts.required' => 'Covered parts are required.',
            'warranty_type.required' => 'Please select a warranty type.',
            'warranty_expire_date.after_or_equal' => 'Expire date must be today or future.',
            'max_usage.integer' => 'Max usage must be a number.',
        ];
    }
}
