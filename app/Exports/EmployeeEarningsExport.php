<?php


namespace App\Exports;

use App\Models\PayrollTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeEarningsExport implements FromCollection, WithHeadings
{
    protected $employeeId;
    protected $periodId;

    public function __construct($employeeId, $periodId = null)
    {
        $this->employeeId = $employeeId;
        $this->periodId = $periodId;
    }

    public function collection()
    {
        $query = PayrollTransaction::with('period')
            ->where('employee_id', $this->employeeId);
        
        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        }
        
        return $query->get()->map(fn($t) => [
            'Period' => $t->period->name,
            'Basic Pay' => $t->basic_pay,
            'Overtime Pay' => $t->overtime_pay,
            'Allowances' => json_encode($t->allowances),
            'Gross Pay' => $t->gross_pay,
            'SSS' => $t->sss_contribution,
            'PhilHealth' => $t->philhealth_contribution,
            'Pag-IBIG' => $t->pagibig_contribution,
            'Tax' => $t->withholding_tax,
            'Net Pay' => $t->net_pay,
        ]);
    }

    public function headings(): array
    {
        return ['Period', 'Basic Pay', 'Overtime Pay', 'Allowances', 'Gross Pay', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Net Pay'];
    }
}