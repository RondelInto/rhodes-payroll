<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTransaction extends Model
{
    protected $fillable = [
        'employee_id', 'period_id', 'basic_pay', 'overtime_pay', 'holiday_pay',
        'allowances', 'gross_pay', 'sss_contribution', 'philhealth_contribution',
        'pagibig_contribution', 'withholding_tax', 'other_deductions',
        'total_deductions', 'net_pay', 'processed_date', 'processed_by'
    ];

    protected $casts = [
        'allowances' => 'array',
        'other_deductions' => 'array',
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'sss_contribution' => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'processed_date' => 'date'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
