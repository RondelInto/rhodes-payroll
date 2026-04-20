<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Company Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Company Information</h3>
                <form action="{{ route('settings.company') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div><label class="input-label">Company Name *</label><input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required class="input-field"></div>
                        <div><label class="input-label">Address *</label><textarea name="company_address" rows="2" required class="input-field">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea></div>
                        <div><label class="input-label">Contact Number *</label><input type="text" name="company_contact" value="{{ old('company_contact', $settings['company_contact'] ?? '') }}" required class="input-field"></div>
                        <div><label class="input-label">Email *</label><input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" required class="input-field"></div>
                        <div><label class="input-label">TIN *</label><input type="text" name="company_tin" value="{{ old('company_tin', $settings['company_tin'] ?? '') }}" required class="input-field"></div>
                        <div><label class="input-label">Company Logo</label><input type="file" name="logo" accept="image/*" class="input-field">
                            @if(!empty($settings['company_logo']))
                                <div class="mt-2"><img src="{{ Storage::url($settings['company_logo']) }}" class="h-16 w-auto"></div>
                            @endif
                        </div>
                        <button type="submit" class="btn-primary w-full">Save Company Info</button>
                    </div>
                </form>
            </div>

            {{-- Payroll Config --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payroll Configuration</h3>
                <form action="{{ route('settings.payroll') }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div><label class="input-label">Working Days per Week</label><input type="number" name="working_days_per_week" value="{{ old('working_days_per_week', $settings['working_days_per_week'] ?? 5) }}" class="input-field"></div>
                        <div><label class="input-label">Working Hours per Day</label><input type="number" step="0.5" name="working_hours_per_day" value="{{ old('working_hours_per_day', $settings['working_hours_per_day'] ?? 8) }}" class="input-field"></div>
                        <div><label class="input-label">Overtime Rate Multiplier</label><input type="number" step="0.01" name="overtime_rate_multiplier" value="{{ old('overtime_rate_multiplier', $settings['overtime_rate_multiplier'] ?? 1.25) }}" class="input-field"></div>
                        <div><label class="input-label">Late Deduction per Hour (₱)</label><input type="number" step="1" name="late_deduction_per_hour" value="{{ old('late_deduction_per_hour', $settings['late_deduction_per_hour'] ?? 50) }}" class="input-field"></div>
                        <div><label class="input-label">Night Differential Rate (e.g., 1.10 = 10% extra)</label><input type="number" step="0.01" name="night_differential_rate" value="{{ old('night_differential_rate', $settings['night_differential_rate'] ?? 1.10) }}" class="input-field"></div>
                        <div><label class="input-label">Holiday Rate Multiplier (e.g., 2.0 = 200%)</label><input type="number" step="0.01" name="holiday_rate_multiplier" value="{{ old('holiday_rate_multiplier', $settings['holiday_rate_multiplier'] ?? 2.0) }}" class="input-field"></div>
                        <button type="submit" class="btn-primary w-full">Save Payroll Settings</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Custom Deductions & Allowances --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Custom Deductions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Custom Deductions</h3>
                    <button onclick="openDeductionModal()" class="btn-secondary text-sm">
                        <i class="fas fa-plus"></i> Add Deduction
                    </button>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700" id="deductionsList">
                    @foreach($deductions as $d)
                    <li class="py-2 flex justify-between items-center" data-id="{{ $d->id }}" data-name="{{ $d->name }}" data-type="{{ $d->type }}" data-amount="{{ $d->amount }}">
                        <span class="text-gray-900 dark:text-white">{{ $d->name }} ({{ $d->type }}: ₱{{ number_format($d->amount,2) }})</span>
                        <div>
                            <button onclick="editDeduction({{ $d->id }})" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteDeduction({{ $d->id }})" class="text-red-600 hover:text-red-800 ml-2"><i class="fas fa-trash"></i></button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Custom Allowances --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Custom Allowances</h3>
                    <button onclick="openAllowanceModal()" class="btn-secondary text-sm">
                        <i class="fas fa-plus"></i> Add Allowance
                    </button>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700" id="allowancesList">
                    @foreach($allowances as $a)
                    <li class="py-2 flex justify-between items-center" data-id="{{ $a->id }}" data-name="{{ $a->name }}" data-type="{{ $a->type }}" data-amount="{{ $a->amount }}">
                        <span class="text-gray-900 dark:text-white">{{ $a->name }} ({{ $a->type }}: ₱{{ number_format($a->amount,2) }})</span>
                        <div>
                            <button onclick="editAllowance({{ $a->id }})" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteAllowance({{ $a->id }})" class="text-red-600 hover:text-red-800 ml-2"><i class="fas fa-trash"></i></button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Modals for Deduction/Allowance --}}
    <div id="deductionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 id="deductionModalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Deduction</h3>
            <form id="deductionForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="deductionMethod" value="POST">
                <div class="space-y-4">
                    <div><label class="input-label">Name</label><input type="text" name="name" id="deduction_name" required class="input-field"></div>
                    <div><label class="input-label">Type</label>
                        <select name="type" id="deduction_type" required class="input-field">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage of Basic Salary</option>
                        </select>
                    </div>
                    <div><label class="input-label">Amount</label><input type="number" step="0.01" name="amount" id="deduction_amount" required class="input-field"></div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeDeductionModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="allowanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 id="allowanceModalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Allowance</h3>
            <form id="allowanceForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="allowanceMethod" value="POST">
                <div class="space-y-4">
                    <div><label class="input-label">Name</label><input type="text" name="name" id="allowance_name" required class="input-field"></div>
                    <div><label class="input-label">Type</label>
                        <select name="type" id="allowance_type" required class="input-field">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage of Basic Salary</option>
                        </select>
                    </div>
                    <div><label class="input-label">Amount</label><input type="number" step="0.01" name="amount" id="allowance_amount" required class="input-field"></div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeAllowanceModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Deductions CRUD
        function openDeductionModal() {
            document.getElementById('deductionForm').reset();
            document.getElementById('deductionForm').action = "{{ route('settings.deductions.store') }}";
            document.getElementById('deductionMethod').value = 'POST';
            document.getElementById('deductionModalTitle').innerText = 'Add Deduction';
            document.getElementById('deductionModal').classList.remove('hidden');
            document.getElementById('deductionModal').classList.add('flex');
        }
        function closeDeductionModal() {
            document.getElementById('deductionModal').classList.add('hidden');
            document.getElementById('deductionModal').classList.remove('flex');
        }
        function editDeduction(id) {
            const li = document.querySelector(`#deductionsList li[data-id='${id}']`);
            if (!li) return;
            const name = li.dataset.name;
            const type = li.dataset.type;
            const amount = li.dataset.amount;
            document.getElementById('deduction_name').value = name;
            document.getElementById('deduction_type').value = type;
            document.getElementById('deduction_amount').value = amount;
            document.getElementById('deductionForm').action = `/settings/deductions/${id}`;
            document.getElementById('deductionMethod').value = 'PUT';
            document.getElementById('deductionModalTitle').innerText = 'Edit Deduction';
            document.getElementById('deductionModal').classList.remove('hidden');
            document.getElementById('deductionModal').classList.add('flex');
        }
        function deleteDeduction(id) {
            if (confirm('Delete this deduction?')) {
                fetch(`/settings/deductions/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => location.reload());
            }
        }

        // Allowances CRUD
        function openAllowanceModal() {
            document.getElementById('allowanceForm').reset();
            document.getElementById('allowanceForm').action = "{{ route('settings.allowances.store') }}";
            document.getElementById('allowanceMethod').value = 'POST';
            document.getElementById('allowanceModalTitle').innerText = 'Add Allowance';
            document.getElementById('allowanceModal').classList.remove('hidden');
            document.getElementById('allowanceModal').classList.add('flex');
        }
        function closeAllowanceModal() {
            document.getElementById('allowanceModal').classList.add('hidden');
            document.getElementById('allowanceModal').classList.remove('flex');
        }
        function editAllowance(id) {
            const li = document.querySelector(`#allowancesList li[data-id='${id}']`);
            if (!li) return;
            const name = li.dataset.name;
            const type = li.dataset.type;
            const amount = li.dataset.amount;
            document.getElementById('allowance_name').value = name;
            document.getElementById('allowance_type').value = type;
            document.getElementById('allowance_amount').value = amount;
            document.getElementById('allowanceForm').action = `/settings/allowances/${id}`;
            document.getElementById('allowanceMethod').value = 'PUT';
            document.getElementById('allowanceModalTitle').innerText = 'Edit Allowance';
            document.getElementById('allowanceModal').classList.remove('hidden');
            document.getElementById('allowanceModal').classList.add('flex');
        }
        function deleteAllowance(id) {
            if (confirm('Delete this allowance?')) {
                fetch(`/settings/allowances/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => location.reload());
            }
        }
    </script>
</x-app-layout>