<?php

namespace App\Http\Controllers\Admin;

use App\Events\StationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Station\StoreStationRequest;
use App\Models\StationStatus;
use App\Services\StationService;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{
    public function __construct(private readonly StationService $stationService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'city', 'is_active']);
        $stations = $this->stationService->getAll($filters);

        return view('admin.stations.index', compact('stations'));
    }

    public function show(Station $station): View
    {
        // Load status and reports (with users) to create the "Profile" view
        $station->load(['status', 'reports.user', 'stationUpdates' => function($q) {
            $q->latest()->take(10);
        }]);
        
        return view('admin.stations.show', compact('station'));
    }

    public function create(): View
    {
        return view('admin.stations.create');
    }

    public function store(StoreStationRequest $request): RedirectResponse
    {
        $this->stationService->create($request->validated());

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station created successfully.');
    }

    public function edit(Station $station): View
    {
        $station->load('status');
        return view('admin.stations.edit', compact('station'));
    }

    public function update(StoreStationRequest $request, Station $station): RedirectResponse
    {
        $this->stationService->update($station, $request->validated());

        return redirect()->back()
            ->with('success', "تم تحديث بيانات المحطة «{$station->name_ar}» بنجاح.");
    }

    public function destroy(Station $station): RedirectResponse
    {
        $this->stationService->delete($station);

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station deleted.');
    }

    public function toggleStatus(Station $station): RedirectResponse
    {
        $station->update(['is_active' => !$station->is_active]);
        $msg = $station->is_active ? 'نشطة الآن' : 'تم إخفاؤها من النظام';
        return back()->with('success', "المحطة «{$station->name_ar}» $msg.");
    }

    public function forceVerify(Station $station): RedirectResponse
    {
        $status = $station->status;

        if (!$status) {
            return back()->with('error', 'هذه المحطة لا تملك حالة وقود بعد.');
        }

        $status->update([
            'source'          => 'admin',
            'last_updated_at' => now(),
            'updated_by'      => auth()->id(),
        ]);

        broadcast(new StationUpdated($station->load('status')))->toOthers();

        return back()->with('success', "تم توثيق محطة «{$station->name_ar}» يدوياً.");
    }
}
