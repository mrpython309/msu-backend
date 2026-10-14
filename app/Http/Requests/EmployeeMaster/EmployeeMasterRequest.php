<?php

namespace App\Http\Requests\EmployeeMaster;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class EmployeeMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('department_id') === '' || $this->input('department_id') === null) {
            $this->merge(['department_id' => null]);
        }

        if ($this->input('email') === '') {
            $this->merge(['email' => null]);
        }
    }

    public function rules(): array
    {
        $ignoreId = $this->route('employeeMaster') ?? $this->route('employee_master');

        return [
            'employee_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'mobile_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employee_masters', 'mobile_number')->ignore($ignoreId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employee_masters', 'email')->ignore($ignoreId),
            ],
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
