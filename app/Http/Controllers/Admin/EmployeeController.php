<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = User::where('role', 'employee')
            ->with('assignedStation')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(Request $request): View
    {
        $stations = Station::active()->orderBy('name_ar')->get(['id', 'name', 'name_ar']);
        $prefilledPhone = $request->query('phone');
        $prefilledStation = $request->query('station_id');
        return view('admin.employees.form', compact('stations', 'prefilledPhone', 'prefilledStation'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'      => 'required|string|max:20',
            'name'       => 'nullable|string|max:100',
            'station_id' => 'required|exists:stations,id',
        ]);

        $phone = User::normalizePhone($request->phone);

        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name'  => $request->name]
        );

        $user->update([
            'role'       => 'employee',
            'station_id' => $request->station_id,
            'name'       => $request->name ?? $user->name,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', "تم إنشاء حساب موظف لـ {$request->phone}");
    }

    public function edit(User $employee): View
    {
        $stations = Station::active()->orderBy('name_ar')->get(['id', 'name', 'name_ar']);
        return view('admin.employees.form', compact('employee', 'stations'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $request->validate([
            'name'       => 'nullable|string|max:100',
            'station_id' => 'required|exists:stations,id',
        ]);

        $employee->update([
            'name'       => $request->name ?? $employee->name,
            'station_id' => $request->station_id,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', "تم تحديث بيانات الموظف {$employee->phone}");
    }

    public function destroy(User $employee): RedirectResponse
    {
        $employee->update(['role' => 'user', 'station_id' => null]);

        return redirect()->route('admin.employees.index')
            ->with('success', "تم إلغاء صلاحية الموظف {$employee->phone}");
    }
}
