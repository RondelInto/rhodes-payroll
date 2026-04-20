@props(['title', 'value', 'icon', 'trend' => null, 'color' => 'blue', 'alert' => false])

@php
    $colorClasses = [
        'blue' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400',
        'green' => 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700 text-green-600 dark:text-green-400',
        'yellow' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700 text-yellow-600 dark:text-yellow-400',
        'purple' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400',
        'red' => 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 text-red-600 dark:text-red-400',
        'emerald' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-300 dark:border-emerald-700 text-emerald-600 dark:text-emerald-400',
    ];
    $iconColor = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="stat-card">
    <div class="flex justify-between items-start">
        <div class="flex-1">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $value }}</p>
            @if($trend)
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 flex items-center gap-1">
                    <i class="fas fa-arrow-up text-xs"></i> {{ $trend }} from last month
                </p>
            @endif
            @if($alert)
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 flex items-center gap-1">
                    <i class="fas fa-exclamation-triangle text-xs"></i> Needs attention
                </p>
            @endif
        </div>
        <div class="rounded-lg p-3 border {{ $iconColor }} shadow-sm">
            <i class="{{ $icon }} text-xl"></i>
        </div>
    </div>
</div>