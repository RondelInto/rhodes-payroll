@props(['href', 'icon'])

@php
    $currentRoute = request()->route()->getName();
    
    // Define which route patterns belong to each menu item
    $activePatterns = [
        route('dashboard') => 'dashboard',
        route('employees.index') => 'employees.*',
        route('departments.index') => 'departments.*',
        route('attendance.index') => 'attendance.*',
        route('periods.index') => 'periods.index',  // only exact match for periods list
        route('periods.calendar') => 'periods.calendar', // exact match for calendar
        route('payroll.index') => 'payroll.*',
        route('reports.index') => 'reports.*',
        route('settings.index') => 'settings.*',
    ];
    
    $isActive = false;
    $pattern = $activePatterns[$href] ?? null;
    
    if ($pattern) {
        if (str_ends_with($pattern, '.*')) {
            $isActive = str_starts_with($currentRoute, rtrim($pattern, '.*'));
        } else {
            $isActive = ($currentRoute === $pattern);
        }
    }
@endphp

<a href="{{ $href }}" 
   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ $isActive ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent hover:border-gray-300 dark:hover:border-gray-600' }}">
    <i class="{{ $icon }} w-5 text-center"></i>
    <span class="ml-3" x-show="sidebarOpen">{{ $slot }}</span>
</a>