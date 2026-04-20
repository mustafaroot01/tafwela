<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Update\AdminUpdateRequest;
use App\Models\Station;
use App\Models\StationUpdate;
use App\Repositories\Contracts\UpdateRepositoryInterface;
use App\Services\UpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateController extends Controller
{
    public function __construct(
        private readonly UpdateRepositoryInterface $updateRepository,
        private readonly UpdateService $updateService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['station_id', 'is_verified', 'is_admin_update']);
        $updates = $this->updateRepository->allPaginated($filters);
        $stations = Station::orderBy('name')->get(['id', 'name']);

        return view('admin.updates.index', compact('updates', 'stations'));
    }

    public function adminUpdate(AdminUpdateRequest $request, Station $station): RedirectResponse
    {
        $this->updateService->submitAdminUpdate($station, $request->user(), $request->validated());

        return back()->with('success', "Station '{$station->name}' updated successfully.");
    }

    public function approve(StationUpdate $update): RedirectResponse
    {
        $this->updateService->approveUserUpdate($update);

        return back()->with('success', 'تم اعتماد التحديث بنجاح.');
    }

    public function approveAll(Request $request): RedirectResponse
    {
        $count = $this->updateService->approveAllUnverified($request->only(['station_id']));

        if ($count === 0) {
            return back()->with('info', 'لا توجد تحديثات غير موثقة حالياً.');
        }

        // Redirect to index without the 'is_verified' filter so they can see the approved updates
        return redirect()->route('admin.updates.index', $request->only(['station_id']))
            ->with('success', "تم اعتماد {$count} تحديثات بنجاح.");
    }

    public function destroy(StationUpdate $update): RedirectResponse
    {
        $this->updateRepository->delete($update);

        return back()->with('success', 'Update deleted.');
    }
}
