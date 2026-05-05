<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Employee Directory</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Manage your workforce</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('employees.index') }}" class="btn-secondary {{ !request()->has('trashed') ? 'bg-blue-100 dark:bg-blue-900/30' : '' }}">
                    <i class="fas fa-users"></i> Active
                </a>
                <a href="{{ route('employees.index', ['trashed' => 'true']) }}" class="btn-secondary {{ request()->has('trashed') ? 'bg-blue-100 dark:bg-blue-900/30' : '' }}">
                    <i class="fas fa-trash-restore"></i> Trash
                </a>
                <button onclick="openEmployeeModal()" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i> Add Employee
                </button>
            </div>
        </div>

        {{-- Filters (only show on active list, not on trash) --}}
        @if(!request()->has('trashed'))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="input-label">Search</label>
                <input type="text" id="searchInput" placeholder="Name, ID, email..." class="input-field">
            </div>
            <div class="w-48">
                <label class="input-label">Department</label>
                <select id="departmentFilter" class="input-field">
                    <option value="">All</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="input-label">Status</label>
                <select id="statusFilter" class="input-field">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="filterEmployees()" class="btn-secondary">
                    <i class="fas fa-filter mr-2"></i> Apply
                </button>
            </div>
        </div>
        @endif

        {{-- Employee Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Photo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Basic Salary</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($employees as $emp)
                        <tr>
                            <td class="px-6 py-4 text-xs font-mono text-gray-900 dark:text-white">{{ $emp->employee_id }}</td>
                            <td class="px-6 py-4">
                                @if($emp->photo)
                                    <img src="{{ Storage::url($emp->photo) }}" class="w-9 h-9 rounded-lg object-cover border border-gray-200" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%239ca3af\' stroke-width=\'1\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\'/%3E%3Ccircle cx=\'12\' cy=\'7\' r=\'4\'/%3E%3C/svg%3E';">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                                        {{ substr($emp->first_name,0,1) }}{{ substr($emp->last_name,0,1) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $emp->full_name }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $emp->department->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $emp->position }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">₱{{ number_format($emp->basic_salary,2) }}</td>
                            <td class="px-6 py-4">
                                <span class="badge-{{ $emp->status == 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($emp->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                @if(!request()->has('trashed'))
                                    <button onclick="editEmployee({{ $emp->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteEmployee({{ $emp->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <button onclick="restoreEmployee({{ $emp->id }})" class="text-green-600 dark:text-green-400 hover:text-green-800">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                    <button onclick="forceDeleteEmployee({{ $emp->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800">
                                        <i class="fas fa-trash-alt"></i> Permanent
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users-slash text-4xl mb-3 block"></i>
                                <p>No employees found.</p>
                                @if(!request()->has('trashed'))
                                    <button onclick="openEmployeeModal()" class="btn-primary mt-4">Add your first employee</button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </tr>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $employees->links() }}
            </div>
        </div>
    </div>

    {{-- Employee Modal (Create/Edit) --}}
    <div id="employeeModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl m-4">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white">Add Employee</h3>
                <button onclick="closeEmployeeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="employeeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" name="id" id="employeeId">

                {{-- Hidden fields for nullable address fields --}}
                <input type="hidden" name="address" value="">
                <input type="hidden" name="city" value="">
                <input type="hidden" name="province" value="">
                <input type="hidden" name="zip_code" value="">

                {{-- Display validation errors --}}
                @if ($errors->any())
                    <div class="mx-6 mt-4 p-3 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="input-label">First Name <span class="text-red-500">*</span></label><input type="text" name="first_name" id="first_name" required class="input-field"></div>
                        <div><label class="input-label">Last Name <span class="text-red-500">*</span></label><input type="text" name="last_name" id="last_name" required class="input-field"></div>
                        <div><label class="input-label">Middle Name</label><input type="text" name="middle_name" id="middle_name" class="input-field"></div>
                        <div><label class="input-label">Email <span class="text-red-500">*</span></label><input type="email" name="email" id="email" required class="input-field"></div>
                        <div><label class="input-label">Phone <span class="text-red-500">*</span></label><input type="text" name="phone" id="phone" required class="input-field"></div>
                        <div><label class="input-label">Date of Birth</label><input type="date" name="date_of_birth" id="date_of_birth" class="input-field"></div>
                        <div><label class="input-label">Gender</label><select name="gender" id="gender" class="input-field"><option>male</option><option>female</option><option>other</option></select></div>
                        <div><label class="input-label">Department</label><select name="department_id" id="department_id" class="input-field">@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                        <div><label class="input-label">Position</label><input type="text" name="position" id="position" class="input-field"></div>
                        <div><label class="input-label">Employment Type</label><select name="employment_type" id="employment_type" class="input-field"><option>regular</option><option>contractual</option><option>probationary</option></select></div>
                        <div><label class="input-label">Hire Date</label><input type="date" name="hire_date" id="hire_date" class="input-field"></div>
                        <div><label class="input-label">Basic Salary</label><input type="number" step="0.01" name="basic_salary" id="basic_salary" class="input-field"></div>
                        <div><label class="input-label">SSS Number</label><input type="text" name="sss_number" id="sss_number" class="input-field"></div>
                        <div><label class="input-label">PhilHealth</label><input type="text" name="philhealth_number" id="philhealth_number" class="input-field"></div>
                        <div><label class="input-label">Pag-IBIG</label><input type="text" name="pagibig_number" id="pagibig_number" class="input-field"></div>
                        <div><label class="input-label">TIN</label><input type="text" name="tin_number" id="tin_number" class="input-field"></div>
                        <div><label class="input-label">Bank Account Number</label><input type="text" name="bank_account" id="bank_account" class="input-field"></div>
                        <div><label class="input-label">Bank Code</label><input type="text" name="bank_code" id="bank_code" class="input-field"></div>
                        <div><label class="input-label">Photo</label><input type="file" name="photo" accept="image/*" id="photoInput" class="input-field"><div id="photoPreview" class="mt-2 hidden"><img id="previewImg" class="w-16 h-16 rounded-lg object-cover border"></div></div>
                        <div><label class="input-label">Status</label><select name="status" id="status" class="input-field"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div><label class="input-label">Shift Start (24h format)</label><input type="time" name="shift_start" id="shift_start" class="input-field" value="09:00"></div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <button type="button" onclick="closeEmployeeModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const storeUrl = "{{ route('employees.store') }}";

        function openEmployeeModal(isEditing = false) {
            if (!isEditing) {
                // Reset only when adding a new employee
                document.getElementById('employeeForm').reset();
                document.getElementById('employeeForm').action = storeUrl;
                document.getElementById('methodField').value = 'POST';
                document.getElementById('modalTitle').innerText = 'Add Employee';
                document.getElementById('photoPreview').classList.add('hidden');
                const errorDiv = document.querySelector('#employeeForm .bg-red-50');
                if (errorDiv) errorDiv.remove();
            }
            document.getElementById('employeeModal').classList.remove('hidden');
            document.getElementById('employeeModal').classList.add('flex');
        }

        function closeEmployeeModal() {
            document.getElementById('employeeModal').classList.add('hidden');
            document.getElementById('employeeModal').classList.remove('flex');
        }

        function editEmployee(id) {
            fetch(`/employees/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    // Populate form fields
                    for (let key in data) {
                        const element = document.getElementById(key);
                        if (element) {
                            element.value = data[key];
                        }
                    }
                    // Set form action and method for update
                    document.getElementById('employeeForm').action = `/employees/${id}`;
                    document.getElementById('methodField').value = 'PUT';
                    document.getElementById('modalTitle').innerText = 'Edit Employee';
                    // Handle photo preview
                    if (data.photo) {
                        document.getElementById('previewImg').src = '/storage/' + data.photo;
                        document.getElementById('photoPreview').classList.remove('hidden');
                    } else {
                        document.getElementById('photoPreview').classList.add('hidden');
                    }
                    // Open modal without resetting
                    openEmployeeModal(true);
                })
                .catch(error => console.error('Error fetching employee:', error));
        }

        function deleteEmployee(id) {
            if (confirm('Move this employee to trash?')) {
                fetch(`/employees/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                }).then(response => {
                    if (response.redirected || response.ok) {
                        location.reload();
                    } else {
                        alert('Error deleting employee.');
                    }
                }).catch(() => alert('Error deleting employee.'));
            }
        }

        function restoreEmployee(id) {
            if (confirm('Restore this employee?')) {
                fetch(`/employees/${id}/restore`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                }).then(response => {
                    if (response.redirected || response.ok) {
                        location.reload();
                    } else {
                        alert('Error restoring employee.');
                    }
                }).catch(() => alert('Error restoring employee.'));
            }
        }

        function forceDeleteEmployee(id) {
            if (confirm('Permanently delete this employee? This action cannot be undone.')) {
                fetch(`/employees/${id}/force`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                }).then(response => {
                    if (response.redirected || response.ok) {
                        location.reload();
                    } else {
                        alert('Error permanently deleting employee.');
                    }
                }).catch(() => alert('Error permanently deleting employee.'));
            }
        }

        function filterEmployees() {
            const search = document.getElementById('searchInput').value;
            const department = document.getElementById('departmentFilter').value;
            const status = document.getElementById('statusFilter').value;
            let url = '{{ route("employees.index") }}?';
            if (search) url += 'search=' + encodeURIComponent(search) + '&';
            if (department) url += 'department=' + department + '&';
            if (status) url += 'status=' + status;
            window.location.href = url;
        }

        document.getElementById('photoInput')?.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('previewImg').src = ev.target.result;
                    document.getElementById('photoPreview').classList.remove('hidden');
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</x-app-layout>