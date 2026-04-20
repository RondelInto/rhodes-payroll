<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $departmentId = $this->route('department')?->id;
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($departmentId)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'annual_budget' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:employees,id',
        ];
    }
}