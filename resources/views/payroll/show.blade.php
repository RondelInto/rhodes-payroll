<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $period->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400">{{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}</p>
            </div>
            <div class="space-x-2">
                <button onclick="location.reload()" class="btn-secondary"><i class="fas fa-sync"></i> Refresh</button>
                @if($period->status == 'draft')
                <form action="{{ route('payroll.process', $period) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn-primary"><i class="fas fa-calculator"></i> Process Payroll</button>
                </form>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Employee</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Basic</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Overtime</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Gross</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">SSS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">PhilHealth</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Pag-IBIG</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Tax</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Deductions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Net Pay</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($transactions as $t)
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $t->employee->full_name }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->basic_pay,2) }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->overtime_pay,2) }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">₱{{ number_format($t->gross_pay,2) }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->sss_contribution,2) }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->philhealth_contribution,2) }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->pagibig_contribution,2) }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->withholding_tax,2) }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">₱{{ number_format($t->total_deductions,2) }}</td>
                            <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($t->net_pay,2) }}</td>
                            <td class="px-4 py-3 space-x-2">
                                <button onclick="viewPayslip({{ $t->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800" title="View Payslip">
                                    <i class="fas fa-receipt"></i>
                                </button>
                                @if($period->status == 'draft')
                                <button onclick="regenerateTransaction({{ $t->id }})" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800" title="Regenerate">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                @endif
                                <a href="{{ route('payroll.download', $t) }}" class="text-green-600 dark:text-green-400 hover:text-green-800" title="Download PDF">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Payslip Modal --}}
    <div id="payslipModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl m-4 p-6 max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700">
            <div id="payslipContent"></div>
            <div class="flex justify-end space-x-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button onclick="closePayslipModal()" class="btn-secondary">Close</button>
                <button onclick="window.print()" class="btn-primary"><i class="fas fa-print mr-2"></i> Print</button>
            </div>
        </div>
    </div>

    <script>
        function viewPayslip(id) {
            fetch(`/payroll/transaction/${id}/payslip-html`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('payslipContent').innerHTML = html;
                    document.getElementById('payslipModal').classList.remove('hidden');
                    document.getElementById('payslipModal').classList.add('flex');
                });
        }
        function closePayslipModal() {
            document.getElementById('payslipModal').classList.add('hidden');
            document.getElementById('payslipModal').classList.remove('flex');
        }
        function regenerateTransaction(id) {
            if (confirm('Regenerate this payroll transaction? This will recalculate the payslip.')) {
                fetch(`/payroll/transaction/${id}/regenerate`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => location.reload());
            }
        }
    </script>
</x-app-layout>