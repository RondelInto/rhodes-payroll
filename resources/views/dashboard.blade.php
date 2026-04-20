<x-app-layout>
    @if(auth()->user()->role === 'admin')
        {{-- Admin Dashboard --}}
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-md p-6 text-white border border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Welcome back, {{ auth()->user()->name }}!</h1>
                        <p class="text-blue-100 text-sm mt-1">Here's what's happening with your payroll today.</p>
                    </div>
                    <i class="fas fa-chalkboard-user text-5xl text-white/20"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <x-stat-card title="Active Employees" value="{{ $totalEmployees }}" icon="fas fa-users" trend="+12%" color="blue" />
                <x-stat-card title="Departments" value="{{ $totalDepartments }}" icon="fas fa-building" color="green" />
                <x-stat-card title="Pending Payroll" value="{{ $pendingPayroll }}" icon="fas fa-clock" color="yellow" alert="{{ $pendingPayroll > 0 }}" />
                <x-stat-card title="Monthly Payroll" value="₱{{ number_format($monthlyPayrollTotal, 2) }}" icon="fas fa-coins" trend="+8%" color="purple" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-chart-bar text-blue-500"></i> Employee Distribution by Department
                        </h3>
                    </div>
                    <div class="p-5">
                        <canvas id="departmentChart" height="250"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-receipt text-blue-500"></i> Recent Payroll Transactions
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/80">
                                <tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Pay</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $t)
                                <tr><td class="px-6 py-3"><div class="flex items-center gap-2"><div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs">{{ substr($t->employee->first_name,0,1) }}</div><span class="text-gray-900 dark:text-white">{{ $t->employee->full_name }}</span></div></td><td class="px-6 py-3 text-gray-900 dark:text-white">{{ $t->period->name }}</td><td class="px-6 py-3 font-semibold text-emerald-600">₱{{ number_format($t->net_pay,2) }}</td><td class="px-6 py-3 text-gray-900 dark:text-white">{{ $t->created_at->format('M d, Y') }}</td></tr>
                                @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No transactions found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('departmentChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($departmentDistribution->pluck('name')),
                        datasets: [{
                            label: 'Number of Employees',
                            data: @json($departmentDistribution->pluck('count')),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            });
        </script>

    @else
        {{-- Regular User Dashboard (simple welcome) --}}
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-xl shadow-md p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}!</h1>
                        <p class="text-emerald-100 text-sm mt-1">Access your payslips and attendance from the sidebar.</p>
                    </div>
                    <i class="fas fa-user-check text-5xl text-white/20"></i>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('my.payslips') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                    <i class="fas fa-receipt text-3xl text-blue-600 mb-3"></i>
                    <h3 class="text-lg font-semibold">My Payslips</h3>
                    <p class="text-gray-500 text-sm mt-1">View and download your payroll history.</p>
                </a>
                <a href="{{ route('my.attendance') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition">
                    <i class="fas fa-calendar-check text-3xl text-green-600 mb-3"></i>
                    <h3 class="text-lg font-semibold">My Attendance</h3>
                    <p class="text-gray-500 text-sm mt-1">Check your daily attendance records.</p>
                </a>
            </div>
        </div>
    @endif
</x-app-layout>