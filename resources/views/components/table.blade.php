@props(['headers' => [], 'emptyMessage' => 'No data available'])

<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        @if(count($headers) > 0)
        <thead class="bg-gray-50 dark:bg-gray-800/80">
            <tr>
                @foreach($headers as $header)
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            {{ $slot }}
            @if($slot->isEmpty())
            <tr>
                <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2 block"></i>
                    <p>{{ $emptyMessage }}</p>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>