<?php

namespace App\Models;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'first_name', 'last_name', 'middle_name', 'email', 'phone',
        'date_of_birth', 'gender', 'address', 'city', 'province', 'zip_code',
        'hire_date', 'department_id', 'position', 'employment_type', 'basic_salary',
        'sss_number', 'philhealth_number', 'pagibig_number', 'tin_number', 'status', 'photo',
        'shift_start',
        'bank_account',   // new
        'bank_code',      // new
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
    ];

    protected $dates = ['deleted_at'];

    // ──────────── ENCRYPTION MUTATORS (SETTERS) ────────────
    public function setBasicSalaryAttribute($value)
    {
        $this->attributes['basic_salary'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setSssNumberAttribute($value)
    {
        $this->attributes['sss_number'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setPhilhealthNumberAttribute($value)
    {
        $this->attributes['philhealth_number'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setPagibigNumberAttribute($value)
    {
        $this->attributes['pagibig_number'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setTinNumberAttribute($value)
    {
        $this->attributes['tin_number'] = $value ? Crypt::encryptString($value) : null;
    }

    // ──────────── ENCRYPTION ACCESSORS (GETTERS) ────────────
    public function getBasicSalaryAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getSssNumberAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getPhilhealthNumberAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getPagibigNumberAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getTinNumberAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    // ──────────── AUDIT TRAIL ────────────
    protected static function booted()
    {
        static::created(function ($employee) {
            static::logAudit('created', $employee);
        });

        static::updated(function ($employee) {
            static::logAudit('updated', $employee, $employee->getOriginal());
        });

        static::deleted(function ($employee) {
            $action = $employee->isForceDeleting() ? 'force_deleted' : 'deleted';
            static::logAudit($action, $employee);
        });

        static::restored(function ($employee) {
            static::logAudit('restored', $employee);
        });
    }

    protected static function logAudit($action, $model, $oldValues = null)
    {
        $user = auth()->user();
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $action === 'updated' ? $model->getAttributes() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // ──────────── RELATIONSHIPS ────────────
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollTransactions(): HasMany
    {
        return $this->hasMany(PayrollTransaction::class);
    }

    public function payrollAdjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Helper to get shift start with default
    public function getShiftStartAttribute($value)
    {
        return $value ?: '09:00';
    }
}