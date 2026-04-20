<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollPeriodRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => 'required|string|max:255',
            'period_type' => 'required|in:weekly,semi-monthly,monthly',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'pay_date'    => 'required|date|after_or_equal:end_date',
            'status'      => 'required|in:draft,processed,paid',
        ];
    }
}