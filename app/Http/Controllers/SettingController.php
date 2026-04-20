<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\CustomDeduction;
use App\Models\CustomAllowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::pluck('value', 'key');
        $deductions = CustomDeduction::all();
        $allowances = CustomAllowance::all();
        return view('settings.index', compact('settings', 'deductions', 'allowances'));
    }

    public function updateCompany(Request $request)
    {
        $data = $request->validate([
            'company_name'    => 'required|string',
            'company_address' => 'required|string',
            'company_contact' => 'required|string',
            'company_email'   => 'required|email',
            'company_tin'     => 'required|string',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = CompanySetting::getValue('company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            CompanySetting::updateOrCreate(['key' => 'company_logo'], ['value' => $logoPath, 'type' => 'image']);
        }

        foreach ($data as $key => $value) {
            if ($key !== 'logo') {
                CompanySetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'text']);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Company info updated.');
    }

    public function updatePayroll(Request $request)
    {
        $data = $request->validate([
            'working_days_per_week'     => 'required|integer|min:1|max:7',
            'working_hours_per_day'     => 'required|numeric|min:1',
            'overtime_rate_multiplier'  => 'required|numeric|min:1',
            'late_deduction_per_hour'   => 'required|numeric|min:0',
            'night_differential_rate'   => 'nullable|numeric|min:1',
            'holiday_rate_multiplier'   => 'nullable|numeric|min:1',
        ]);

        foreach ($data as $key => $value) {
            CompanySetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'number']);
        }

        return redirect()->route('settings.index')->with('success', 'Payroll settings updated.');
    }

    // ──────────── CUSTOM DEDUCTIONS ────────────
    public function storeDeduction(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
        ]);
        CustomDeduction::create($request->all());
        return redirect()->route('settings.index')->with('success', 'Deduction added.');
    }

    public function updateDeduction(Request $request, CustomDeduction $deduction)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
        ]);
        $deduction->update($request->all());
        return redirect()->route('settings.index')->with('success', 'Deduction updated.');
    }

    public function destroyDeduction(CustomDeduction $deduction)
    {
        $deduction->delete();
        return redirect()->route('settings.index')->with('success', 'Deduction deleted.');
    }

    // ──────────── CUSTOM ALLOWANCES ────────────
    public function storeAllowance(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
        ]);
        CustomAllowance::create($request->all());
        return redirect()->route('settings.index')->with('success', 'Allowance added.');
    }

    public function updateAllowance(Request $request, CustomAllowance $allowance)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
        ]);
        $allowance->update($request->all());
        return redirect()->route('settings.index')->with('success', 'Allowance updated.');
    }

    public function destroyAllowance(CustomAllowance $allowance)
    {
        $allowance->delete();
        return redirect()->route('settings.index')->with('success', 'Allowance deleted.');
    }
}