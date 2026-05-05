<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Attendance</h1>
            <div class="flex items-center space-x-2">
                <form method="GET" class="flex items-center space-x-2">
                    <select name="period_id" onchange="this.form.submit()" class="input-field">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" @selected($selectedPeriod == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Quick Log for Today --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Log Today's Attendance</h2>
            <form method="POST" action="{{ route('my.attendance.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <input type="hidden" name="date" value="{{ today()->toDateString() }}">
                <div>
                    <label class="input-label">Date</label>
                    <input type="text" value="{{ today()->format('Y-m-d') }}" disabled class="input-field bg-gray-100 dark:bg-gray-700">
                </div>
                <div>
                    <label class="input-label">Time In</label>
                    <input type="time" name="time_in" class="input-field">
                </div>
                <div>
                    <label class="input-label">Time Out</label>
                    <input type="time" name="time_out" class="input-field">
                </div>
                <div>
                    <label class="input-label">Status</label>
                    <select name="status" class="input-field">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="half-day">Half Day</option>
                        <option value="holiday">Holiday</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <button type="submit" class="btn-primary">Log Attendance</button>
                </div>
            </form>
        </div>

        {{-- Existing Attendance Records for Selected Period --}}
        @if(!$selectedPeriod || !$period)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 text-yellow-800 dark:text-yellow-300">
                No attendance records found for any payroll period.
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800/80">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Time In</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Time Out</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($attendances as $att)
                                <tr>
                                    <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $att->date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $att->time_in ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $att->time_out ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $att->hours_worked }}</td>
                                    <td class="px-6 py-4 text-gray-900 dark:text-white">{{ ucfirst($att->status) }}</td>
                                    <td class="px-6 py-4 space-x-2">
                                        <button onclick="openEditModal({{ $att }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('my.attendance.destroy', $att) }}" method="POST" class="inline" onsubmit="return confirm('Delete this record?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No attendance records for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Edit Attendance</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="input-label">Date</label>
                        <input type="text" id="edit_date" disabled class="input-field bg-gray-100 dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="input-label">Time In</label>
                        <input type="time" name="time_in" id="edit_time_in" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Time Out</label>
                        <input type="time" name="time_out" id="edit_time_out" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Status</label>
                        <select name="status" id="edit_status" class="input-field">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half-day">Half Day</option>
                            <option value="holiday">Holiday</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeEditModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(att) {
            document.getElementById('edit_date').value = att.date;
            document.getElementById('edit_time_in').value = att.time_in || '';
            document.getElementById('edit_time_out').value = att.time_out || '';
            document.getElementById('edit_status').value = att.status;
            document.getElementById('editForm').action = `/my-attendance/${att.id}`;
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }
    </script>
</x-app-layout>