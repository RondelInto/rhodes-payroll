<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('employee')?->id;
        return [
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => ['required', 'email', Rule::unique('employees')->ignore($id)],
            'phone'            => 'required|string',
            'date_of_birth'    => 'nullable|date|before:today',
            'gender'           => 'required|in:male,female,other',
            'address'          => 'nullable|string',
            'city'             => 'nullable|string',
            'province'         => 'nullable|string',
            'zip_code'         => 'nullable|string',
            'hire_date'        => 'nullable|date',
            'department_id'    => 'required|exists:departments,id',
            'position'         => 'required|string',
            'employment_type'  => 'required|in:regular,contractual,probationary,project-based',
            'basic_salary'     => 'required|numeric|min:0',
            'sss_number'       => 'nullable|string',
            'philhealth_number' => 'nullable|string',
            'pagibig_number'   => 'nullable|string',
            'tin_number'       => 'nullable|string',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'           => 'required|in:active,inactive',
            'shift_start'      => 'nullable|date_format:H:i',
        ];
    }
}