<div class="p-8 font-sans">
    <div class="text-center border-b pb-4">
        <h2 class="text-2xl font-bold">{{ $company['company_name'] ?? 'Rhodes Corporation' }}</h2>
        <p>{{ $company['company_address'] ?? '' }}</p>
        <p>TIN: {{ $company['company_tin'] ?? '' }}</p>
        <h3 class="text-xl mt-2">PAYSLIP</h3>
        <p>Period: {{ $transaction->period->name }}</p>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div><strong>Employee:</strong> {{ $transaction->employee->full_name }}</div>
        <div><strong>Position:</strong> {{ $transaction->employee->position }}</div>
        <div><strong>Employee ID:</strong> {{ $transaction->employee->employee_id }}</div>
        <div><strong>Pay Date:</strong> {{ $transaction->period->pay_date->format('M d, Y') }}</div>
    </div>
    <table class="w-full mt-6 border-collapse">
        <tr class="border-b"><th class="text-left py-2">Earnings</th><th class="text-right">Amount</th><th class="text-left pl-4">Deductions</th><th class="text-right">Amount</th></tr>
        <tr class="border-b"><td class="py-1">Basic Pay</td><td class="text-right">₱{{ number_format($transaction->basic_pay,2) }}</td><td class="pl-4">SSS</td><td class="text-right">₱{{ number_format($transaction->sss_contribution,2) }}</td></tr>
        <tr class="border-b"><td class="py-1">Overtime Pay</td><td class="text-right">₱{{ number_format($transaction->overtime_pay,2) }}</td><td class="pl-4">PhilHealth</td><td class="text-right">₱{{ number_format($transaction->philhealth_contribution,2) }}</td></tr>
        <tr class="border-b"><td class="py-1">Holiday Pay</td><td class="text-right">₱{{ number_format($transaction->holiday_pay,2) }}</td><td class="pl-4">Pag-IBIG</td><td class="text-right">₱{{ number_format($transaction->pagibig_contribution,2) }}</td></tr>
        <tr class="border-b"><td class="py-1">Allowances</td><td class="text-right">₱{{ number_format(array_sum((array)$transaction->allowances),2) }}</td><td class="pl-4">Withholding Tax</td><td class="text-right">₱{{ number_format($transaction->withholding_tax,2) }}</td></tr>
        <tr class="border-t font-bold"><td class="py-1">Gross Pay</td><td class="text-right">₱{{ number_format($transaction->gross_pay,2) }}</td><td class="pl-4">Total Deductions</td><td class="text-right">₱{{ number_format($transaction->total_deductions,2) }}</td></tr>
        <tr class="bg-blue-50"><td colspan="3" class="py-2 font-bold">NET PAY</td><td class="text-right font-bold text-blue-700">₱{{ number_format($transaction->net_pay,2) }}</td></tr>
    </table>
    <div class="text-center text-xs text-gray-500 mt-8">This is a computer-generated payslip. No signature required.</div>
</div>