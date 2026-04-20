<?php


namespace App\Exports;

use App\Models\PayrollTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeductionsExport implements FromCollection, WithHeadings
{
    protected $periodId;

    public function __construct($periodId)
    {
        $this->periodId = $periodId;
    }

    public function collection()
    {
        return PayrollTransaction::with('employee')
            ->where('period_id', $this->periodId)
            ->get()
            ->map(fn($t) => [
                $t->employee->full_name,
                $t->sss_contribution,
                $t->philhealth_contribution,
                $t->pagibig_contribution,
                $t->withholding_tax,
                $t->total_deductions,
            ]);
    }

    public function headings(): array
    {
        return ['Employee', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Withholding Tax', 'Total Deductions'];
    }
}