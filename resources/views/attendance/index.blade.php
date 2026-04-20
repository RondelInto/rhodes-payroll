<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Attendance</h1>
            <div class="flex space-x-2">
                <form method="GET" class="flex items-center space-x-2">
                    <select name="period_id" onchange="this.form.submit()" class="input-field">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" @selected($selectedPeriod == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </form>
                <button onclick="openImportModal()" class="btn-secondary"><i class="fas fa-upload"></i> Import CSV</button>
                <button onclick="saveAll()" class="btn-primary"><i class="fas fa-save"></i> Save All</button>
            </div>
        </div>

        <div>
            <input type="text" id="employeeSearch" placeholder="Search employee by name or ID..." class="input-field w-64" onkeyup="filterEmployees()">
        </div>

        @if(!$selectedPeriod || !$period)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 text-yellow-800 dark:text-yellow-300">
                Please select a valid payroll period.
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Department</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        @foreach($employees as $employee)
                            @php
                                $empData = $employeeAttendance[$employee->id] ?? null;
                                if (!$empData) continue;
                                $dates   = $empData['dates'];
                                $records = $empData['records'];
                            @endphp
                            <tbody x-data="{ open: false }" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <tr>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                                        {{ $employee->full_name }}<br>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->employee_id }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $employee->department->name }}</td>
                                    <td class="px-4 py-3">
                                        <button @click="open = !open" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                            <span x-show="!open">View Attendance <i class="fas fa-chevron-down"></i></span>
                                            <span x-show="open">Hide <i class="fas fa-chevron-up"></i></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr x-show="open" x-cloak class="bg-gray-50 dark:bg-gray-800/50">
                                    <td colspan="3" class="px-0 py-0">
                                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                                            <form method="POST" action="{{ route('attendance.store') }}" class="attendance-form" data-employee="{{ $employee->id }}" data-period="{{ $selectedPeriod }}">
                                                @csrf
                                                <input type="hidden" name="period_id" value="{{ $selectedPeriod }}">
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                                        <thead class="bg-gray-100 dark:bg-gray-700">
                                                            <tr>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Date</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Time In</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Time Out</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Hours</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Late</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">OT</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                            @foreach($dates as $date)
                                                                @php
                                                                    $dateStr = $date->format('Y-m-d');
                                                                    $record = $records[$dateStr] ?? null;
                                                                @endphp
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $dateStr }}
                                                                        <input type="hidden" name="date[]" value="{{ $dateStr }}">
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        <div class="flex items-center gap-1">
                                                                            <input type="time" name="time_in[]" value="{{ $record?->time_in ?? '' }}" class="input-field w-28 text-sm dark:bg-gray-700 dark:text-white">
                                                                            <button type="button" class="now-time-in bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-medium px-2 py-1 rounded-md transition shadow-sm">Now</button>
                                                                            <button type="button" class="clear-time-in bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-medium px-2 py-1 rounded-md transition shadow-sm">Clear</button>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        <div class="flex items-center gap-1">
                                                                            <input type="time" name="time_out[]" value="{{ $record?->time_out ?? '' }}" class="input-field w-28 text-sm dark:bg-gray-700 dark:text-white">
                                                                            <button type="button" class="now-time-out bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-medium px-2 py-1 rounded-md transition shadow-sm">Now</button>
                                                                            <button type="button" class="clear-time-out bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-medium px-2 py-1 rounded-md transition shadow-sm">Clear</button>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">{{ $record?->hours_worked ?? 0 }}</td>
                                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">{{ $record?->late_hours ?? 0 }}</td>
                                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">{{ $record?->overtime_hours ?? 0 }}</td>
                                                                    <td class="px-3 py-2">
                                                                        <select name="status[]" class="input-field text-sm dark:bg-gray-700 dark:text-white">
                                                                            <option value="present" @selected($record?->status == 'present')>Present</option>
                                                                            <option value="absent" @selected($record?->status == 'absent')>Absent</option>
                                                                            <option value="late" @selected($record?->status == 'late')>Late</option>
                                                                            <option value="half-day" @selected($record?->status == 'half-day')>Half Day</option>
                                                                            <option value="holiday" @selected($record?->status == 'holiday')>Holiday</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="mt-3 text-right">
                                                    <button type="submit" class="btn-secondary text-sm">Save this employee</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- CSV Import Modal --}}
    <div id="importModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Import Attendance CSV</h3>
            <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="period_id" value="{{ $selectedPeriod }}">
                <div class="mb-4">
                    <label class="input-label">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="input-field">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: employee_id,date,time_in,time_out,status</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeImportModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.getElementById('importModal').classList.add('flex');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.getElementById('importModal').classList.remove('flex');
        }

        function filterEmployees() {
            const search = document.getElementById('employeeSearch').value.toLowerCase();
            const tbodies = document.querySelectorAll('tbody');
            tbodies.forEach(tbody => {
                const nameCell = tbody.querySelector('tr:first-child td:first-child');
                if (!nameCell) return;
                const name = nameCell.innerText.toLowerCase();
                if (name.includes(search)) {
                    tbody.style.display = '';
                } else {
                    tbody.style.display = 'none';
                }
            });
        }

        function getCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        function saveEmployeeAttendance(employeeId) {
            const form = document.querySelector(`.attendance-form[data-employee="${employeeId}"]`);
            if (!form) return;
            const formData = new FormData(form);
            fetch('{{ route("attendance.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.success) location.reload();
                else alert('Error saving attendance.');
            }).catch(() => alert('Error saving attendance.'));
        }

        function saveAll() {
            document.querySelectorAll('.attendance-form').forEach(form => {
                const employeeId = form.dataset.employee;
                saveEmployeeAttendance(employeeId);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                if (e.target.classList.contains('now-time-in')) {
                    const timeInput = e.target.closest('td').querySelector('input[type="time"]');
                    if (timeInput) timeInput.value = getCurrentTime();
                } else if (e.target.classList.contains('now-time-out')) {
                    const timeInput = e.target.closest('td').querySelector('input[type="time"]');
                    if (timeInput) timeInput.value = getCurrentTime();
                } else if (e.target.classList.contains('clear-time-in')) {
                    const timeInput = e.target.closest('td').querySelector('input[type="time"]');
                    if (timeInput) timeInput.value = '';
                } else if (e.target.classList.contains('clear-time-out')) {
                    const timeInput = e.target.closest('td').querySelector('input[type="time"]');
                    if (timeInput) timeInput.value = '';
                }
            });
        });
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>