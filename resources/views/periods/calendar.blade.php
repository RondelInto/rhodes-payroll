<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payroll Calendar</h1>
            <a href="{{ route('periods.index') }}" class="btn-secondary">
                <i class="fas fa-list mr-2"></i> List View
            </a>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div id="calendar"></div>
        </div>
    </div>

    @php
        $events = $periods->map(function($p) {
            return [
                'title' => $p->name,
                'start' => $p->start_date,
                'end' => $p->end_date,
                'color' => $p->status == 'draft' ? '#f59e0b' : ($p->status == 'processed' ? '#3b82f6' : '#10b981'),
                'url' => route('payroll.show', $p)
            ];
        });
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: @json($events),
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });
            calendar.render();
        });
    </script>
</x-app-layout>