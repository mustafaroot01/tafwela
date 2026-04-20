<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\StationUpdate;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users'    => User::where('is_admin', false)->count(),
            'total_stations' => Station::count(),
            'active_stations'=> Station::where('is_active', true)->count(),
            'updates_today'  => StationUpdate::whereDate('created_at', today())->count(),
            'banned_users'   => User::where('is_banned', true)->count(),
            'verified_updates' => StationUpdate::where('is_verified', true)->active()->count(),
        ];

        $recentUpdates = StationUpdate::with(['station', 'user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $topStations = Station::withCount('updates')
            ->orderByDesc('updates_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUpdates', 'topStations'));
    }
}
