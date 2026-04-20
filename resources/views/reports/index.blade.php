<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Reports</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Payroll Summary Report --}}
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4"><i class="fas fa-chart-line mr-2"></i> Payroll Summary</h3>
                <form action="{{ route('reports.payroll-summary') }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div><label>Payroll Period *</label>
                            <select name="period_id" required class="input-field w-full">
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label>Department (Optional)</label>
                            <select name="department_id" class="input-field w-full">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary w-full">Export CSV</button>
                    </div>
                </form>
            </div>

            {{-- Employee Earnings Report --}}
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4"><i class="fas fa-user mr-2"></i> Employee Earnings</h3>
                <form action="{{ route('reports.employee-earnings') }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div><label>Employee *</label>
                            <select name="employee_id" required class="input-field w-full">
                                @foreach(\App\Models\Employee::all() as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label>Period (Optional)</label>
                            <select name="period_id" class="input-field w-full">
                                <option value="">All Periods</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary w-full">Export CSV</button>
                    </div>
                </form>
            </div>

            {{-- Deductions Report --}}
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4"><i class="fas fa-calculator mr-2"></i> Deductions Report</h3>
                <form action="{{ route('reports.deductions') }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div><label>Payroll Period *</label>
                            <select name="period_id" required class="input-field w-full">
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary w-full">Export CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>