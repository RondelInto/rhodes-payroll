<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: true, darkMode: false }" x-init="
    if (localStorage.getItem('darkMode') === 'true') { darkMode = true; document.documentElement.classList.add('dark'); }
    $watch('darkMode', val => { localStorage.setItem('darkMode', val); val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark'); })
">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rhodes Payroll</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg transition-all duration-300"
               :class="{ 'w-20': !sidebarOpen, 'w-64': sidebarOpen }">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3" :class="{ 'justify-center w-full': !sidebarOpen }">
                        @php
                            $logo = \App\Models\CompanySetting::getValue('company_logo');
                        @endphp
                        @if($logo)
                            <img src="{{ Storage::url($logo) }}" class="h-8 w-auto">
                        @else
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow">
                                <i class="fas fa-chalkboard-user text-white text-sm"></i>
                            </div>
                        @endif
                        <span x-show="sidebarOpen" class="text-gray-800 dark:text-white font-bold text-lg">Rhodes</span>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-chevron-left text-sm" :class="{ 'rotate-180': !sidebarOpen }"></i>
                    </button>
                </div>

                <nav class="flex-1 py-6 px-3 space-y-1.5">
                    @if(auth()->user()->role === 'admin')
                        <x-nav-link href="{{ route('dashboard') }}" icon="fas fa-home">Home</x-nav-link>
                        <x-nav-link href="{{ route('employees.index') }}" icon="fas fa-users">Employees</x-nav-link>
                        <x-nav-link href="{{ route('departments.index') }}" icon="fas fa-building">Departments</x-nav-link>
                        <x-nav-link href="{{ route('attendance.index') }}" icon="fas fa-clock">Attendance</x-nav-link>
                        <x-nav-link href="{{ route('periods.index') }}" icon="fas fa-calendar-alt">Payroll Periods</x-nav-link>
                        <x-nav-link href="{{ route('periods.calendar') }}" icon="fas fa-calendar-week">Calendar</x-nav-link>
                        <x-nav-link href="{{ route('payroll.index') }}" icon="fas fa-calculator">Payroll</x-nav-link>
                        <x-nav-link href="{{ route('reports.index') }}" icon="fas fa-chart-line">Reports</x-nav-link>
                        <x-nav-link href="{{ route('settings.index') }}" icon="fas fa-sliders-h">Settings</x-nav-link>
                        <x-nav-link href="{{ route('notifications.history') }}" icon="fas fa-bell">Notifications</x-nav-link>
                    @else
                        <x-nav-link href="{{ route('dashboard') }}" icon="fas fa-home">Home</x-nav-link>
                        <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700"></div>
                        <x-nav-link href="{{ route('my.payslips') }}" icon="fas fa-receipt">My Payslips</x-nav-link>
                        <x-nav-link href="{{ route('my.attendance') }}" icon="fas fa-calendar-check">My Attendance</x-nav-link>
                        <x-nav-link href="{{ route('my.profile') }}" icon="fas fa-user-edit">My Profile</x-nav-link>
                        <x-nav-link href="{{ route('notifications.history') }}" icon="fas fa-bell">Notifications</x-nav-link>
                    @endif
                </nav>

                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="darkMode = !darkMode" class="flex items-center w-full px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                        <span x-show="sidebarOpen" class="ml-3 text-sm">Dark Mode</span>
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden" :class="{ 'ml-20': !sidebarOpen, 'ml-64': sidebarOpen }">
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16 flex items-center justify-between px-6 shadow-sm">
                <form action="{{ route('employees.index') }}" method="GET" class="relative max-w-md w-full">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" placeholder="Search employees..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </form>
                <div class="flex items-center space-x-4">
                    {{-- Dynamic Notification Bell --}}
                    <div class="relative" x-data="notificationComponent()" x-init="init()">
                        <button @click="toggleDropdown()" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-bell text-gray-600 dark:text-gray-300 text-lg"></i>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center ring-2 ring-white"></span>
                        </button>
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50">
                            <div class="p-3 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-900 dark:text-white flex justify-between items-center">
                                <span>Notifications</span>
                                <button @click="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800">Mark all as read</button>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700" @click="markAsRead(notification.id, notification.data.url)">
                                        <p class="text-sm text-gray-900 dark:text-white" x-text="notification.data.title"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="notification.data.message"></p>
                                        <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notification.created_at)"></p>
                                    </div>
                                </template>
                                <div x-show="notifications.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    No new notifications
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow">{{ substr(auth()->user()->name, 0, 1) }}</div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 z-50">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button></form>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success')) <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg shadow-sm">{{ session('success') }}</div> @endif
                @if(session('error')) <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg shadow-sm">{{ session('error') }}</div> @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    <div x-data="{ show: false, message: '', type: 'success' }" x-init="@if(session('success')) show=true; message='{{ session('success') }}'; type='success'; setTimeout(()=>show=false,3000); @endif @if(session('error')) show=true; message='{{ session('error') }}'; type='error'; setTimeout(()=>show=false,3000); @endif" x-show="show" x-transition.duration.300ms class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-lg shadow-lg text-white" :class="{ 'bg-green-600': type === 'success', 'bg-red-600': type === 'error' }" x-text="message"></div>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        function notificationComponent() {
    return {
        dropdownOpen: false,
        unreadCount: 0,
        notifications: [],
        init() {
            this.fetchNotifications();
            setInterval(() => this.fetchNotifications(), 30000);
        },
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
            if (this.dropdownOpen) {
                this.fetchNotifications();
            }
        },
        fetchNotifications() {
            fetch('{{ url("/notifications/api") }}', {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                this.notifications = data;
                this.unreadCount = data.length;
            });
        },
        markAsRead(id, url) {
            fetch(`/notifications/api/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => {
                this.fetchNotifications();
                if (url) window.location.href = url;
            });
        },
        markAllAsRead() {
            fetch('{{ url("/notifications/api/read-all") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => this.fetchNotifications());
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} minutes ago`;
            if (diffHours < 24) return `${diffHours} hours ago`;
            return `${diffDays} days ago`;
        }
    }
}
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>