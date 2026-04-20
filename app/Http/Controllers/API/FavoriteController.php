<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favorites()
            ->with('status')
            ->get();

        return response()->json(StationResource::collection($favorites));
    }

    public function toggle(Request $request, int $stationId): JsonResponse
    {
        $station = Station::find($stationId);

        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        $user = $request->user();
        $status = $user->favorites()->toggle($stationId);

        $isFavorited = count($status['attached']) > 0;

        return response()->json([
            'is_favorited' => $isFavorited,
            'message'      => $isFavorited ? 'Added to favorites.' : 'Removed from favorites.',
        ]);
    }
}
