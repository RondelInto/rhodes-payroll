<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notification History</h1>
            <form action="{{ url('/notifications/history/mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary text-sm">Mark all as read</button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($notifications->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800/80">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Message</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Received</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($notifications as $notification)
                            <tr>
                                <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ $notification->data['title'] ?? 'Notification' }}</td>
                                <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $notification->data['message'] ?? '' }}</td>
                                <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $notification->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4">
                                    @if($notification->read_at)
                                        <span class="badge-inactive">Read</span>
                                    @else
                                        <span class="badge-active">Unread</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-x-2">
                                    @if(!$notification->read_at)
                                        <form action="{{ url('/notifications/history/'.$notification->id.'/read') }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800" title="Mark as read">✓ Read</button>
                                        </form>
                                    @else
                                        <form action="{{ url('/notifications/history/'.$notification->id.'/unread') }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Mark as unread">↺ Unread</button>
                                        </form>
                                    @endif
                                    <form action="{{ url('/notifications/history/'.$notification->id.'/delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete this notification?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">✗ Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-bell-slash text-4xl mb-3 block"></i>
                    <p>No notifications yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>