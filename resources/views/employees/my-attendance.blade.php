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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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
</x-app-layout>