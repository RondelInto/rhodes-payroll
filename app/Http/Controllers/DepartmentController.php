<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\DepartmentRequest;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    public function store(DepartmentRequest $request)
    {
        Department::create($request->validated());
        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());
        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    // Soft delete
    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Cannot delete department with employees.');
        }
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department moved to trash.');
    }

    // Restore soft deleted department
    public function restore($id)
    {
        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();
        return redirect()->route('departments.index')->with('success', 'Department restored.');
    }

    // Permanently delete
    public function forceDelete($id)
    {
        $department = Department::withTrashed()->findOrFail($id);
        $department->forceDelete();
        return redirect()->route('departments.index')->with('success', 'Department permanently deleted.');
    }
}