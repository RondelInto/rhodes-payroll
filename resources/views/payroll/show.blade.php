<x-app-layout>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $period->name }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}
                </p>
            </div>

            <div class="space-x-2">
                <button onclick="location.reload()" class="btn-secondary">
                    <i class="fas fa-sync"></i> Refresh
                </button>

                @if($period->status == 'draft')
                <form action="{{ route('payroll.process', $period) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-calculator"></i> Process Payroll
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- One‑Off Adjustments Section (only for draft periods) --}}
        @if($period->status == 'draft')
        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">
                    One-Off Adjustments
                </h3>

                <button onclick="openAdjustmentModal()" class="btn-secondary text-sm">
                    <i class="fas fa-plus"></i> Add Adjustment
                </button>
            </div>

            <p class="text-sm text-blue-600 dark:text-blue-300 mb-3">
                Add bonuses or deductions for specific employees this period.
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-blue-100 dark:bg-blue-800/50">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-800 dark:text-gray-200">Employee</th>
                            <th class="px-3 py-2 text-left text-gray-800 dark:text-gray-200">Type</th>
                            <th class="px-3 py-2 text-left text-gray-800 dark:text-gray-200">Amount</th>
                            <th class="px-3 py-2 text-left text-gray-800 dark:text-gray-200">Description</th>
                            <th class="px-3 py-2 text-left text-gray-800 dark:text-gray-200">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-blue-200 dark:divide-blue-700">
                        @php
                            $adjustments = \App\Models\PayrollAdjustment::where('period_id', $period->id)->with('employee')->get();
                        @endphp

                        @forelse($adjustments as $adj)
                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-800/30">
                            <td class="px-3 py-1 text-gray-900 dark:text-white">
                                {{ $adj->employee->full_name }}
                            </td>

                            <td class="px-3 py-1 capitalize text-gray-900 dark:text-white">
                                {{ $adj->type }}
                            </td>

                            <td class="px-3 py-1 text-gray-900 dark:text-white">
                                ₱{{ number_format($adj->amount,2) }}
                            </td>

                            <td class="px-3 py-1 text-gray-900 dark:text-white">
                                {{ $adj->description ?? '—' }}
                            </td>

                            <td class="px-3 py-1 space-x-2">
                                <button onclick="editAdjustment({{ $adj }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('payroll.adjustments.destroy', $adj) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-gray-500 dark:text-gray-400">
                                No adjustments added yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Transactions Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
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

                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($transactions as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
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
                            <button onclick="viewPayslip({{ $t->id }})" class="text-blue-600 dark:text-blue-400">
                                <i class="fas fa-receipt"></i>
                            </button>

                            @if($period->status == 'draft')
                            <button onclick="regenerateTransaction({{ $t->id }})" class="text-yellow-600 dark:text-yellow-400">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            @endif

                            <a href="{{ route('payroll.download', $t) }}" class="text-green-600 dark:text-green-400">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-6 text-gray-500 dark:text-gray-400">
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payslip Modal --}}
    <div id="payslipModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-xl p-6 max-w-3xl w-full border border-gray-200 dark:border-gray-700">
            <div id="payslipContent"></div>
            <div class="flex justify-end mt-4 space-x-2">
                <button onclick="closePayslipModal()" class="btn-secondary">Close</button>
                <button onclick="window.print()" class="btn-primary">Print</button>
            </div>
        </div>
    </div>

    {{-- Adjustment Modal --}}
    <div id="adjustmentModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full border border-gray-200 dark:border-gray-700">
            <h3 id="adjustmentModalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Adjustment</h3>
            <form id="adjustmentForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="adjustmentMethod" value="POST">
                <input type="hidden" name="period_id" value="{{ $period->id }}">
                <div class="space-y-4">
                    <div>
                        <label class="input-label">Employee <span class="text-red-500">*</span></label>
                        <select name="employee_id" id="adj_employee_id" required class="input-field w-full">
                            @foreach(\App\Models\Employee::where('status', 'active')->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Type <span class="text-red-500">*</span></label>
                        <select name="type" id="adj_type" required class="input-field w-full">
                            <option value="bonus">Bonus (adds to gross)</option>
                            <option value="deduction">Deduction (subtracts from net)</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="amount" id="adj_amount" required class="input-field w-full">
                    </div>
                    <div>
                        <label class="input-label">Description</label>
                        <input type="text" name="description" id="adj_description" class="input-field w-full" placeholder="e.g., Performance bonus, Loan deduction">
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeAdjustmentModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Payslip modal
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

        // Adjustment modal
        function openAdjustmentModal() {
            document.getElementById('adjustmentForm').reset();
            document.getElementById('adjustmentForm').action = '{{ route("payroll.adjustments.store") }}';
            document.getElementById('adjustmentMethod').value = 'POST';
            document.getElementById('adjustmentModalTitle').innerText = 'Add Adjustment';
            document.getElementById('adjustmentModal').classList.remove('hidden');
            document.getElementById('adjustmentModal').classList.add('flex');
        }
        function closeAdjustmentModal() {
            document.getElementById('adjustmentModal').classList.add('hidden');
            document.getElementById('adjustmentModal').classList.remove('flex');
        }
        function editAdjustment(adj) {
            openAdjustmentModal();
            document.getElementById('adjustmentModalTitle').innerText = 'Edit Adjustment';
            document.getElementById('adjustmentForm').action = `/payroll/adjustments/${adj.id}`;
            document.getElementById('adjustmentMethod').value = 'PUT';
            document.getElementById('adj_employee_id').value = adj.employee_id;
            document.getElementById('adj_type').value = adj.type;
            document.getElementById('adj_amount').value = adj.amount;
            document.getElementById('adj_description').value = adj.description || '';
        }
    </script>
</x-app-layout>