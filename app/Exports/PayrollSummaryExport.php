<?php


namespace App\Exports;

use App\Models\PayrollTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollSummaryExport implements FromQuery, WithHeadings, WithMapping
{
    protected $periodId;
    protected $departmentId;

    public function __construct($periodId, $departmentId = null)
    {
        $this->periodId = $periodId;
        $this->departmentId = $departmentId;
    }

    public function query()
    {
        $query = PayrollTransaction::with('employee.department')
            ->where('period_id', $this->periodId);
        
        if ($this->departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
        }
        
        return $query;
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Employee Name', 'Department', 'Position',
            'Basic Pay', 'Overtime', 'Gross Pay', 'SSS', 'PhilHealth',
            'Pag-IBIG', 'Withholding Tax', 'Total Deductions', 'Net Pay'
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee->employee_id,
            $row->employee->full_name,
            $row->employee->department->name,
            $row->employee->position,
            number_format($row->basic_pay, 2),
            number_format($row->overtime_pay, 2),
            number_format($row->gross_pay, 2),
            number_format($row->sss_contribution, 2),
            number_format($row->philhealth_contribution, 2),
            number_format($row->pagibig_contribution, 2),
            number_format($row->withholding_tax, 2),
            number_format($row->total_deductions, 2),
            number_format($row->net_pay, 2),
        ];
    }
}