<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Http\Requests\PayrollPeriodRequest;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->paginate(15);
        return view('periods.index', compact('periods'));
    }

    public function store(PayrollPeriodRequest $request)
    {
        // Check for duplicate period (same start and end date)
        $exists = PayrollPeriod::where('start_date', $request->start_date)
                    ->where('end_date', $request->end_date)
                    ->exists();
        if ($exists) {
            return back()->with('error', 'A payroll period with these dates already exists.');
        }

        PayrollPeriod::create($request->validated());
        return redirect()->route('periods.index')->with('success', 'Period created.');
    }

    public function update(PayrollPeriodRequest $request, PayrollPeriod $period)
    {
        if ($period->status !== 'draft') {
            return back()->with('error', 'Only draft periods can be edited.');
        }

        // Check for duplicate period (excluding current period)
        $exists = PayrollPeriod::where('start_date', $request->start_date)
                    ->where('end_date', $request->end_date)
                    ->where('id', '!=', $period->id)
                    ->exists();
        if ($exists) {
            return back()->with('error', 'Another payroll period with these dates already exists.');
        }

        $period->update($request->validated());
        return redirect()->route('periods.index')->with('success', 'Period updated.');
    }

    public function destroy(PayrollPeriod $period)
    {
        if ($period->status !== 'draft') {
            return back()->with('error', 'Cannot delete processed/paid period.');
        }
        $period->delete();
        return redirect()->route('periods.index')->with('success', 'Period deleted.');
    }

    public function calendar()
    {
        $periods = PayrollPeriod::all();
        return view('periods.calendar', compact('periods'));
    }

    public function show($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        return redirect()->route('payroll.show', $period);
    }
}