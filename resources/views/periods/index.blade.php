<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payroll Periods</h1>
            <button onclick="openModal()" class="btn-primary"><i class="fas fa-plus mr-2"></i> New Period</button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Start - End</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Pay Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($periods as $period)
                        <tr>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $period->name }}</td>
                            <td class="px-6 py-4 capitalize text-gray-900 dark:text-white">{{ $period->period_type }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $period->pay_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($period->status == 'draft') badge-draft
                                    @elseif($period->status == 'processed') badge-processed
                                    @else badge-paid @endif">
                                    {{ ucfirst($period->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="{{ route('payroll.show', $period) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($period->status == 'draft')
                                    <button onclick='editPeriod(@json($period))' class="text-green-600 dark:text-green-400 hover:text-green-800">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="{{ route('periods.destroy', $period) }}" method="POST" class="inline" onsubmit="return confirm('Delete this period?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $periods->links() }}
            </div>
        </div>
    </div>

    {{-- Modal for Create/Edit – styled like Add Employee modal --}}
    <div id="periodModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl m-4">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-t-xl">
                <h3 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white">Add Payroll Period</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="periodForm" method="POST" action="{{ route('periods.store') }}">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Period Name (auto-generated, read-only) --}}
                        <div class="col-span-2">
                            <label class="input-label">Period Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required class="input-field w-full bg-gray-100 dark:bg-gray-700" readonly>
                        </div>

                        {{-- Month/Year picker --}}
                        <div>
                            <label class="input-label">Select Month/Year <span class="text-red-500">*</span></label>
                            <input type="month" id="monthPicker" class="input-field w-full">
                        </div>

                        {{-- Period Type --}}
                        <div>
                            <label class="input-label">Period Type <span class="text-red-500">*</span></label>
                            <select name="period_type" id="period_type" required class="input-field w-full">
                                <option value="weekly">Weekly</option>
                                <option value="semi-monthly">Semi-monthly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        {{-- Half selector (semi-monthly only) --}}
                        <div id="halfSelector" style="display: none;">
                            <label class="input-label">Half <span class="text-red-500">*</span></label>
                            <select id="half" class="input-field w-full">
                                <option value="first">First Half (1st–15th)</option>
                                <option value="second">Second Half (16th–last day)</option>
                            </select>
                        </div>

                        {{-- Week start date (weekly only) --}}
                        <div id="weekStartSelector" style="display: none;">
                            <label class="input-label">Week Start Date <span class="text-red-500">*</span></label>
                            <input type="date" id="weekStart" class="input-field w-full">
                        </div>

                        {{-- Date fields (start, end, pay) – three columns on desktop --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 col-span-2">
                            <div>
                                <label class="input-label">Start Date <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" id="start_date" required class="input-field w-full bg-gray-100 dark:bg-gray-700" readonly>
                            </div>
                            <div>
                                <label class="input-label">End Date <span class="text-red-500">*</span></label>
                                <input type="date" name="end_date" id="end_date" required class="input-field w-full bg-gray-100 dark:bg-gray-700" readonly>
                            </div>
                            <div>
                                <label class="input-label">Pay Date <span class="text-red-500">*</span></label>
                                <input type="date" name="pay_date" id="pay_date" required class="input-field w-full">
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="col-span-2">
                            <label class="input-label">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required class="input-field w-full">
                                <option value="draft">Draft</option>
                                <option value="processed">Processed</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-xl">
                    <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save Period</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let isEditing = false;
        let listenersInitialized = false;

        function openModal() {
            document.getElementById('periodModal').classList.remove('hidden');
            document.getElementById('periodModal').classList.add('flex');
            document.getElementById('periodForm').reset();
            isEditing = false;
            document.getElementById('methodField').value = 'POST';
            document.getElementById('periodForm').action = '{{ route("periods.store") }}';
            document.getElementById('modalTitle').innerText = 'Add Payroll Period';
            document.getElementById('name').readOnly = false;
            document.getElementById('name').style.backgroundColor = '';
            document.getElementById('start_date').readOnly = false;
            document.getElementById('end_date').readOnly = false;

            const today = new Date();
            const defaultMonth = today.toISOString().slice(0,7);
            document.getElementById('monthPicker').value = defaultMonth;
            document.getElementById('weekStart').value = '';

            if (!listenersInitialized) {
                document.getElementById('period_type').addEventListener('change', toggleExtraFields);
                document.getElementById('period_type').addEventListener('change', autoFillDates);
                document.getElementById('monthPicker').addEventListener('change', autoFillDates);
                document.getElementById('half').addEventListener('change', autoFillDates);
                document.getElementById('weekStart').addEventListener('change', autoFillDates);
                listenersInitialized = true;
            }
            toggleExtraFields();
            autoFillDates();
        }

        function closeModal() {
            document.getElementById('periodModal').classList.add('hidden');
            document.getElementById('periodModal').classList.remove('flex');
        }

        function editPeriod(period) {
            openModal();
            isEditing = true;
            document.getElementById('modalTitle').innerText = 'Edit Period';
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('periodForm').action = `/periods/${period.id}`;
            document.getElementById('name').value = period.name;
            document.getElementById('name').readOnly = true;
            document.getElementById('name').style.backgroundColor = '#e5e7eb';
            document.getElementById('period_type').value = period.period_type;
            document.getElementById('start_date').value = period.start_date.split(' ')[0];
            document.getElementById('end_date').value = period.end_date.split(' ')[0];
            document.getElementById('pay_date').value = period.pay_date.split(' ')[0];
            document.getElementById('status').value = period.status;

            const start = new Date(period.start_date);
            const monthYear = `${start.getFullYear()}-${String(start.getMonth()+1).padStart(2,'0')}`;
            document.getElementById('monthPicker').value = monthYear;

            if (period.period_type === 'semi-monthly') {
                const day = new Date(period.start_date).getDate();
                document.getElementById('half').value = (day === 1) ? 'first' : 'second';
            }
            if (period.period_type === 'weekly') {
                document.getElementById('weekStart').value = period.start_date.split(' ')[0];
            }
            toggleExtraFields();
        }

        function toggleExtraFields() {
            const periodType = document.getElementById('period_type').value;
            const halfSelector = document.getElementById('halfSelector');
            const weekStartSelector = document.getElementById('weekStartSelector');
            halfSelector.style.display = periodType === 'semi-monthly' ? 'block' : 'none';
            weekStartSelector.style.display = periodType === 'weekly' ? 'block' : 'none';
        }

        function formatLocalDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function adjustPayDate(date) {
            const day = date.getDay();
            if (day === 6) date.setDate(date.getDate() - 1);
            if (day === 0) date.setDate(date.getDate() - 2);
            return date;
        }

        function autoFillDates() {
            if (isEditing) return;

            const periodType = document.getElementById('period_type').value;
            const monthYear = document.getElementById('monthPicker').value;
            if (!monthYear) return;

            const [year, month] = monthYear.split('-').map(Number);
            let startDate = null;
            let endDate = null;
            let payDate = null;
            const payDateOffset = 5;

            if (periodType === 'monthly') {
                startDate = new Date(year, month - 1, 1);
                endDate = new Date(year, month, 0);
                payDate = new Date(endDate);
                payDate.setDate(endDate.getDate() + payDateOffset);
                payDate = adjustPayDate(payDate);
            }
            else if (periodType === 'semi-monthly') {
                const half = document.getElementById('half').value;
                if (half === 'first') {
                    startDate = new Date(year, month - 1, 1);
                    endDate = new Date(year, month - 1, 15);
                } else {
                    startDate = new Date(year, month - 1, 16);
                    endDate = new Date(year, month, 0);
                }
                payDate = new Date(endDate);
                payDate.setDate(endDate.getDate() + payDateOffset);
                payDate = adjustPayDate(payDate);
            }
            else if (periodType === 'weekly') {
                let weekStart = document.getElementById('weekStart').value;
                if (!weekStart) {
                    weekStart = formatLocalDate(new Date(year, month - 1, 1));
                    document.getElementById('weekStart').value = weekStart;
                }
                startDate = new Date(weekStart);
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                payDate = new Date(endDate);
                payDate.setDate(endDate.getDate() + payDateOffset);
                payDate = adjustPayDate(payDate);
            }

            if (startDate && endDate) {
                if (endDate < startDate) {
                    alert('End date cannot be before start date. Please adjust the week start date.');
                    return;
                }
                document.getElementById('start_date').value = formatLocalDate(startDate);
                document.getElementById('end_date').value = formatLocalDate(endDate);
                document.getElementById('name').value = `${formatLocalDate(startDate)} - ${formatLocalDate(endDate)}`;
            }
            if (payDate) {
                document.getElementById('pay_date').value = formatLocalDate(payDate);
            }
        }
    </script>
</x-app-layout>