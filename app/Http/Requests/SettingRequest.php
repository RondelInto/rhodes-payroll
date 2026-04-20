<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_name' => 'sometimes|required|string',
            'company_address' => 'sometimes|required|string',
            'company_contact' => 'sometimes|required|string',
            'company_email' => 'sometimes|required|email',
            'company_tin' => 'sometimes|required|string',
            'working_days_per_week' => 'sometimes|required|integer|min:1|max:7',
            'working_hours_per_day' => 'sometimes|required|numeric|min:1',
            'overtime_rate_multiplier' => 'sometimes|required|numeric|min:1',
            'late_deduction_per_hour' => 'sometimes|required|numeric|min:0',
        ];
    }
}