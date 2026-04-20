<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Audit Logs</h1>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th>User</th><th>Action</th><th>Model</th><th>ID</th><th>Changes</th><th>IP</th><th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td><span class="badge-{{ $log->action }}">{{ str_replace('_', ' ', $log->action) }}</span></td>
                            <td>{{ class_basename($log->model_type) }}</td>
                            <td>{{ $log->model_id }}</td>
                            <td>
                                @if($log->old_values || $log->new_values)
                                    <details><summary>View</summary>
                                    <pre class="text-xs">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }} → {{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>