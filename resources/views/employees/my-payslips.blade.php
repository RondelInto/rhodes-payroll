<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Payslips</h1>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Gross Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Net Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($transactions as $t)
                        <tr>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $t->period->name }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">₱{{ number_format($t->gross_pay,2) }}</td>
                            <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($t->net_pay,2) }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('payroll.download', $t) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-download"></i> PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>