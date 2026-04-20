<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Departments</h1>
            <button onclick="openModal()" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Department
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Budget (Annual)</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($departments as $dept)
                        <tr>
                            <td class="px-6 py-4 text-sm font-mono text-gray-900 dark:text-white">{{ $dept->code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $dept->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $dept->employees_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">₱{{ number_format($dept->annual_budget ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $dept->manager?->full_name ?? 'Not assigned' }}</td>
                            <td class="px-6 py-4 space-x-2">
                                <button onclick="editDepartment({{ $dept }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this department?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $departments->links() }}
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div id="departmentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Department</h3>
            <form id="departmentForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <div class="space-y-4">
                    <div>
                        <label class="input-label">Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" required class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Description</label>
                        <textarea name="description" id="description" rows="2" class="input-field"></textarea>
                    </div>
                    <div>
                        <label class="input-label">Annual Budget</label>
                        <input type="number" step="0.01" name="annual_budget" id="annual_budget" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Department Manager</label>
                        <select name="manager_id" id="manager_id" class="input-field">
                            <option value="">None</option>
                            @foreach(\App\Models\Employee::all() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('departmentModal').classList.remove('hidden');
            document.getElementById('departmentModal').classList.add('flex');
        }
        function closeModal() {
            document.getElementById('departmentModal').classList.add('hidden');
            document.getElementById('departmentModal').classList.remove('flex');
        }
        function editDepartment(dept) {
            openModal();
            document.getElementById('modalTitle').innerText = 'Edit Department';
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('departmentForm').action = `/departments/${dept.id}`;
            document.getElementById('code').value = dept.code;
            document.getElementById('name').value = dept.name;
            document.getElementById('description').value = dept.description || '';
            document.getElementById('annual_budget').value = dept.annual_budget || '';
            document.getElementById('manager_id').value = dept.manager_id || '';
        }
    </script>
</x-app-layout>