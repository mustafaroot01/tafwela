<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StationReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = StationReport::with(['user', 'station']);

        if ($request->station_id) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->whereHas('station', function($sq) use ($s) {
                    $sq->where('name_ar', 'like', "%$s%")
                       ->orWhere('name', 'like', "%$s%")
                       ->orWhere('city', 'like', "%$s%");
                })->orWhereHas('user', function($uq) use ($s) {
                    $uq->where('name', 'like', "%$s%");
                });
            });
        }

        $reports = $query->latest()->paginate(15);

        // Calculate duplicate count manually for accuracy
        foreach ($reports as $report) {
            $report->duplicates_count = StationReport::where('station_id', $report->station_id)
                ->where('reason', $report->reason)
                ->where('status', 'pending')
                ->count();
        }
            
        return view('admin.reports.index', compact('reports'));
    }

    public function updateStatus(Request $request, $id)
    {
        $report = StationReport::findOrFail($id);
        $report->update(['status' => $request->status]);
        
        return back()->with('success', 'Report status updated.');
    }

    public function destroy($id)
    {
        StationReport::findOrFail($id)->delete();
        return back()->with('success', 'Report deleted.');
    }
}
