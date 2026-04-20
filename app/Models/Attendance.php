<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    // Explicitly set the table name to match the migration
    protected $table = 'attendances';

    protected $fillable = [
    'employee_id', 'period_id', 'date', 'time_in', 'time_out',
    'hours_worked', 'late_hours', 'overtime_hours', 'status'
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'late_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }
}