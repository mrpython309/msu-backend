<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEmployeeStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        if (is_object($employeeId)) {
            $employeeId = $employeeId->id;
        }

        return [
            'name' => 'required|string|max:255',
            'emp_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'emp_code')->ignore($employeeId),
            ],
            'date_of_joining' => 'required|date',
            'mobile_number' => [
                'required',
                'digits_between:10,12',
                Rule::unique('users', 'mobile_number')->ignore($employeeId),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($employeeId),
            ],
            'designation_id' => 'required|exists:designations,id',
            'is_department_head' => 'nullable',
            'company_id' => ['required', 'array', 'min:1'],
            'company_id.*' => ['exists:companies,id'],
            'branch_id' => ['required', 'array', 'min:1'],
            'branch_id.*' => ['exists:branches,id'],
            'department_id' => ['required', 'array', 'min:1'],
            'department_id.*' => ['exists:departments,id'],
            'employee_status' => 'required|in:active,inactive',
        ];
    }
}
