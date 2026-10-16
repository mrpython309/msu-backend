<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow request
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vendors', 'phone_number')->ignore($this->vendor), // ignore on update
            ],
            'contact_person' => 'nullable|string|max:255',
            'email' => ['nullable', 'string', 'max:255'],
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'brands'=>'nullable|array',
        ];
    }
}
