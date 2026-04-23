<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Update\SubmitUpdateRequest;
use App\Http\Resources\UpdateResource;
use App\Models\Station;
use App\Models\StationUpdate;
use App\Services\UpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateService $updateService) {}

    public function store(SubmitUpdateRequest $request, int $stationId): JsonResponse
    {
        $station = Station::active()->find($stationId);

        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        $user = $request->user();
        $data = array_merge($request->validated(), ['ip_address' => $request->ip()]);

        // Employee assigned to this station → immediate update, bypasses confirmation
        if ($user->isEmployee() && (int) $user->station_id === (int) $stationId) {
            $result = $this->updateService->submitEmployeeUpdate($station, $user, $data);
            if (!$result['success']) {
                return response()->json(['message' => $result['message']], 429);
            }
            return response()->json(new UpdateResource($result['update']), 201);
        }

        $result = $this->updateService->submitUserUpdate($station, $user, $data);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 429);
        }

        return response()->json(new UpdateResource($result['update']), 201);
    }

    public function confirm(Request $request, int $updateId): JsonResponse
    {
        return $this->interact($request, $updateId, 'confirm');
    }

    public function interact(Request $request, int $stationId): JsonResponse
    {
        $station = Station::find($stationId);
        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        // Find the most recent active user update to interact with
        $update = StationUpdate::active()
            ->where('station_id', $stationId)
            ->where('is_admin_update', false)
            ->orderByDesc('created_at')
            ->first();

        if (!$update) {
            return response()->json(['message' => 'لا يوجد تحديث نشط لتقييمه.'], 404);
        }

        if ($update->user_id === $request->user()->id) {
            return response()->json(['message' => 'لا يمكنك تقييم التحديث الخاص بك.'], 422);
        }

        $interactionType = $request->input('type');
        if (!in_array($interactionType, ['confirm', 'dispute'])) {
             return response()->json(['message' => 'Invalid interaction type.'], 422);
        }

        $result = $this->updateService->interactWithUpdate($update, $request->user(), $interactionType);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(new UpdateResource($result['update']));
    }

    public function forStation(int $stationId): JsonResponse
    {
        $station = Station::find($stationId);

        if (!$station) {
            return response()->json(['message' => 'Station not found.'], 404);
        }

        $updates = StationUpdate::with('user')
            ->where('station_id', $stationId)
            ->active()
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'data' => UpdateResource::collection($updates),
            'meta' => ['total' => $updates->total(), 'current_page' => $updates->currentPage()],
        ]);
    }
}
