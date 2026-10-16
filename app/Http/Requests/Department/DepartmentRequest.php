<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DepartmentRequest extends FormRequest
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
            'department_name' => [
                'required',
                'string',
                'max:255',
                // Ignore current department on update
                Rule::unique('departments', 'department_name')->ignore($this->department),
            ],
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Handle validation failure for BOTH web & API
     */
    protected function failedValidation(Validator $validator)
    {
        // If request expects JSON → API response
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        // Otherwise → normal web redirect with errors
        parent::failedValidation($validator);
    }
}
