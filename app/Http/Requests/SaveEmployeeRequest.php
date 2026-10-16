<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class SaveEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        $employeeId = $this->route('employee'); // resource route binding

        // If route-model binding is used
        if (is_object($employeeId)) {
            $employeeId = $employeeId->id;
        }

        return [

            /* ============ BASIC DETAILS ============ */

            'name'   => 'required|string|max:255',

            'emp_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'emp_code')->ignore($employeeId),
            ],

            'date_of_joining'=> 'required|date',

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
            'is_department_head'=>'nullable',

            /* ============ USER ACCOUNT ============ */

            'is_user' => 'required|in:yes,no',

           'password' => [
                            Rule::requiredIf(fn () =>
                                $this->isMethod('post') && $this->input('is_user') === 'yes'
                            ),
                            'nullable',
                            'min:8'
                        ],

            'role_id' => 'required_if:is_user,yes|exists:roles,id|nullable',

            /* ============ ORGANIZATION ============ */

            'company_id'        => ['required', 'array', 'min:1'],
            'company_id.*'      => ['exists:companies,id'],
            'branch_id'        => ['required', 'array', 'min:1'],
            'branch_id.*'      => ['exists:branches,id'],

            'department_id'    => ['required', 'array', 'min:1'],
            'department_id.*'  => ['exists:departments,id'],

            /* ============ STATUS ============ */

            'employee_status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required_if' => 'Password is required when user login is enabled.',
            'role_id.required_if' => 'Role is required when user login is enabled.',
        ];
    }
}
