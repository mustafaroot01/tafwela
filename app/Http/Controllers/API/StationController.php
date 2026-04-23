<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Models\StationReport;
use App\Services\StationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function __construct(private readonly StationService $stationService) {}

    public function index(Request $request): JsonResponse
    {
        $stations = $this->stationService->getAll($request->only(['fuel_type', 'congestion', 'all']));
        return response()->json(StationResource::collection($stations));
    }

    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat'        => ['required', 'numeric', 'between:-90,90'],
            'lng'        => ['required', 'numeric', 'between:-180,180'],
            'radius'     => ['nullable', 'numeric', 'min:1', 'max:50'],
            'fuel_type'    => ['nullable'],
            'fuel_type.*'  => ['string', 'in:petrol,petrol_normal,petrol_improved,petrol_super,diesel,kerosene,gas'],
            'congestion'   => ['nullable', 'in:low,medium,high'],
        ]);

        $stations = $this->stationService->getNearby(
            (float) $request->lat,
            (float) $request->lng,
            (float) ($request->radius ?? 10),
            [
                'fuel_type'  => $request->input('fuel_type'),
                'congestion' => $request->input('congestion'),
            ]
        );

        return response()->json(StationResource::collection($stations));
    }

    public function show(int $id): JsonResponse
    {
        $station = $this->stationService->find($id);

        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        return response()->json(new StationResource($station));
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $stations = $this->stationService->search($request->q);

        return response()->json(StationResource::collection($stations));
    }

    public function importRoute(Request $request): JsonResponse
    {
        $request->validate([
            'points' => ['required', 'array', 'min:1'],
            'points.*.lat' => ['required', 'numeric'],
            'points.*.lng' => ['required', 'numeric'],
        ]);

        $stations = $this->stationService->importAlongRoute($request->points);

        return response()->json([
            'message' => 'Route scan completed.',
            'data' => StationResource::collection($stations),
        ]);
    }

    public function report(Request $request, int $stationId): JsonResponse
    {
        $request->validate([
            'reason'  => ['required', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $report = StationReport::create([
            'user_id'    => $request->user()->id,
            'station_id' => $stationId,
            'reason'     => $request->reason,
            'comment'    => $request->comment,
        ]);

        // Send Telegram notification
        app(\App\Services\TelegramService::class)->notifyReport($report->load(['station', 'user']));

        return response()->json(['message' => 'Report submitted successfully.']);
    }
}
