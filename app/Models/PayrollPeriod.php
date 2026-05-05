<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $table = 'payroll_periods';
    
    protected $fillable = [
        'name', 'period_type', 'start_date', 'end_date', 'pay_date', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(PayrollTransaction::class, 'period_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'period_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class, 'period_id');
    }

    /**
     * Check if the payroll period is already processed (locked).
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }
}