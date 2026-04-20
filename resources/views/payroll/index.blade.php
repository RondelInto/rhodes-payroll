<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payroll Processing</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage and process payroll periods</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($periods as $period)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-200">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $period->name }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($period->status == 'draft') badge-draft
                                @elseif($period->status == 'processed') badge-processed
                                @else badge-paid @endif">
                                {{ ucfirst($period->status) }}
                            </span>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300 mb-4">
                            <p><i class="fas fa-calendar-alt w-5"></i> {{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}</p>
                            <p><i class="fas fa-money-bill-wave w-5"></i> Pay Date: {{ $period->pay_date->format('M d, Y') }}</p>
                            <p><i class="fas fa-chart-line w-5"></i> Transactions: {{ $period->transactions_count ?? 0 }}</p>
                        </div>
                        <div class="flex gap-2">
                            @if($period->status == 'draft')
                                <form action="{{ route('payroll.process', $period) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full btn-primary text-center">
                                        <i class="fas fa-calculator mr-1"></i> Process
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('payroll.show', $period) }}" class="flex-1 btn-secondary text-center">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-3 block"></i>
                    <p class="text-gray-500 dark:text-gray-400">No payroll periods found.</p>
                    <a href="{{ route('periods.index') }}" class="btn-primary mt-4 inline-block">
                        <i class="fas fa-plus mr-2"></i> Create Payroll Period
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>