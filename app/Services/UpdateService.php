<?php

namespace App\Services;

use App\Events\StationUpdated;
use App\Models\AppSetting;
use App\Models\Station;
use App\Models\StationStatus;
use App\Models\StationUpdate;
use App\Models\UpdateInteraction;
use App\Models\User;
use App\Repositories\Contracts\UpdateRepositoryInterface;
use App\Services\FcmV1Service;

class UpdateService
{
    private function expiryMinutes(): int
    {
        return (int) AppSetting::get('update_expiry_minutes', 60);
    }

    private function verificationThreshold(): int
    {
        return (int) AppSetting::get('verification_threshold', 3);
    }

    public function __construct(
        private readonly UpdateRepositoryInterface $updateRepository,
        private readonly TelegramService $telegramService,
    ) {}

    /**
     * Regular user update — stored but NOT applied until confirmation threshold reached.
     */
    public function submitUserUpdate(Station $station, User $user, array $data): array
    {
        // 1. If user is an employee of THIS station, they should never be rate limited
        if ($user->isEmployee() && (int) $user->station_id === (int) $station->id) {
            // This is a fallback; usually handled by submitEmployeeUpdate
            return $this->submitEmployeeUpdateInternal($station, $user, $data);
        }

        // 2. Check for recent updates to THIS specific station
        $limit = (int) AppSetting::get('user_hourly_limit', 3);
        $recentUpdatesCount = StationUpdate::where('station_id', $station->id)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentUpdatesCount >= $limit) {
            return [
                'success' => false, 
                'message' => "لقد وصلت للحد الأقصى لتحديث هذه المحطة ($limit مرات في الساعة). يرجى المحاولة لاحقاً."
            ];
        }

        // 2.5 Check for recent interaction (like/dislike) on THIS station
        $cooldown = (int) AppSetting::get('interaction_cooldown_minutes', 60);
        $hasRecentInteraction = UpdateInteraction::where('user_id', $user->id)
            ->whereHas('stationUpdate', function($q) use ($station) {
                $q->where('station_id', $station->id);
            })
            ->where('created_at', '>=', now()->subMinutes($cooldown))
            ->exists();

        if ($hasRecentInteraction) {
            return ['success' => false, 'message' => "لقد قمت بتقييم هذه المحطة مؤخراً. الرجاء الانتظار لمدة $cooldown دقيقة قبل تحديثها."];
        }

        // 3. Global anti-spam: Check for recent update to ANY station
        $globalCooldown = (int) AppSetting::get('global_update_cooldown', 5);
        if ($this->updateRepository->hasAnyRecentUpdateFromUser($user->id, $globalCooldown)) {
            return ['success' => false, 'message' => "يرجى الانتظار لمدة $globalCooldown دقائق بين كل تحديث وآخر."];
        }

        // 4. Check for an identical existing unverified update to confirm it instead of duplicating
        // This makes "Multiple reports = Verification" logic work naturally
        // We use a looser check to handle partial matches better
        $existingUpdate = StationUpdate::where('station_id', $station->id)
            ->where('is_verified', false)
            ->where('is_admin_update', false)
            ->where('petrol_normal', $data['petrol_normal'] ?? null)
            ->where('petrol_improved', $data['petrol_improved'] ?? null)
            ->where('petrol_super', $data['petrol_super'] ?? null)
            ->where('diesel', $data['diesel'] ?? null)
            ->where('kerosene', $data['kerosene'] ?? null)
            ->where('gas', $data['gas'] ?? null)
            ->active()
            ->orderByDesc('created_at')
            ->first();

        if ($existingUpdate && (int) $existingUpdate->user_id !== (int) $user->id) {
            // Check if congestion also matches (if provided)
            if (!isset($data['congestion']) || $existingUpdate->congestion === $data['congestion']) {
                $res = $this->interactWithUpdate($existingUpdate, $user, 'confirm');
                if ($res['success']) {
                    return ['success' => true, 'message' => 'تم تأكيد التحديث الحالي من قبلك أيضاً!', 'update' => $res['update']];
                }
            }
        }

        $update = $this->updateRepository->create([
            'station_id'      => $station->id,
            'user_id'         => $user->id,
            'petrol'          => $data['petrol'] ?? null,
            'petrol_normal'   => $data['petrol_normal'] ?? null,
            'petrol_improved' => $data['petrol_improved'] ?? null,
            'petrol_super'    => $data['petrol_super'] ?? null,
            'diesel'          => $data['diesel'] ?? null,
            'kerosene'        => $data['kerosene'] ?? null,
            'gas'             => $data['gas'] ?? null,
            'congestion'      => $data['congestion'] ?? null,
            'is_verified'     => false,
            'ip_address'      => $data['ip_address'] ?? null,
            'device_id'       => $data['device_id'] ?? null,
            'expires_at'      => now()->addMinutes($this->expiryMinutes()),
        ]);

        $user->increment('update_count');

        // Update the station's public status so others can see and vote on it
        $this->applyBestAvailableStatus($station);
        $station->load('status');
        broadcast(new StationUpdated($station))->toOthers();

        // Send Telegram notification
        $this->telegramService->notifyUpdate($update->load(['station', 'user']));

        return ['success' => true, 'update' => $update];
    }

    /**
     * Employee update — immediate, bypasses confirmation (like admin, source='employee').
     * Employee can only update their assigned station (validated in controller).
     */
    public function submitEmployeeUpdate(Station $station, User $employee, array $data): array
    {
        return $this->submitEmployeeUpdateInternal($station, $employee, $data);
    }

    private function submitEmployeeUpdateInternal(Station $station, User $employee, array $data): array
    {
        // Limit employees to configurable updates per hour
        $limit = (int) AppSetting::get('employee_hourly_limit', 10);
        $recentUpdatesCount = StationUpdate::where('station_id', $station->id)
            ->where('user_id', $employee->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentUpdatesCount >= $limit) {
            return [
                'success' => false, 
                'message' => "لقد وصلت للحد الأقصى للتحديثات ($limit مرات في الساعة). يرجى المحاولة لاحقاً."
            ];
        }

        $update = $this->updateRepository->create([
            'station_id'      => $station->id,
            'user_id'         => $employee->id,
            'petrol'          => $data['petrol'] ?? null,
            'petrol_normal'   => $data['petrol_normal'] ?? null,
            'petrol_improved' => $data['petrol_improved'] ?? null,
            'petrol_super'    => $data['petrol_super'] ?? null,
            'diesel'          => $data['diesel'] ?? null,
            'kerosene'        => $data['kerosene'] ?? null,
            'gas'             => $data['gas'] ?? null,
            'congestion'      => $data['congestion'] ?? null,
            'is_admin_update' => true,
            'is_verified'     => true,
            'expires_at'      => now()->addMinutes($this->expiryMinutes()),
        ]);

        $employee->increment('update_count');
        $this->applyStatusDirectly($station, $employee, $data, 'employee');
        $station->load('status');
        broadcast(new StationUpdated($station))->toOthers();
        $this->notifyAllUsers($station);

        // Send Telegram notification
        $this->telegramService->notifyUpdate($update->load(['station', 'user']));

        return ['success' => true, 'update' => $update];
    }

    /**
     * Admin update — immediate, full trust.
     */
    public function submitAdminUpdate(Station $station, User $admin, array $data): StationUpdate
    {
        $update = $this->updateRepository->create([
            'station_id'      => $station->id,
            'user_id'         => $admin->id,
            'petrol'          => $data['petrol'] ?? null,
            'petrol_normal'   => $data['petrol_normal'] ?? null,
            'petrol_improved' => $data['petrol_improved'] ?? null,
            'petrol_super'    => $data['petrol_super'] ?? null,
            'diesel'          => $data['diesel'] ?? null,
            'kerosene'        => $data['kerosene'] ?? null,
            'gas'             => $data['gas'] ?? null,
            'congestion'      => $data['congestion'] ?? null,
            'is_admin_update' => true,
            'is_verified'     => true,
            'expires_at'      => now()->addMinutes($this->expiryMinutes()),
        ]);

        $this->applyStatusDirectly($station, $admin, $data, 'admin');
        $station->load('status');
        broadcast(new StationUpdated($station))->toOthers();
        $this->notifyAllUsers($station);

        return $update;
    }

    /**
     * Admin manually approves a user update.
     */
    public function approveUserUpdate(StationUpdate $update): StationUpdate
    {
        $update->update([
            'is_verified' => true,
            'is_admin_update' => false, // Keep it as user update but verified
        ]);

        $station = $update->station;
        $this->applyStatusDirectly($station, null, [
            'petrol'          => $update->petrol,
            'petrol_normal'   => $update->petrol_normal,
            'petrol_improved' => $update->petrol_improved,
            'petrol_super'    => $update->petrol_super,
            'diesel'          => $update->diesel,
            'kerosene'        => $update->kerosene,
            'gas'             => $update->gas,
            'congestion'      => $update->congestion,
        ], 'verified_users');

        $station->load('status');
        broadcast(new StationUpdated($station))->toOthers();
        $this->notifyAllUsers($station);

        return $update;
    }

    /**
     * Admin manually approves all unverified user updates (optionally filtered).
     */
    public function approveAllUnverified(array $filters = []): int
    {
        $query = StationUpdate::where('is_verified', false)
            ->where('is_admin_update', false);

        if (!empty($filters['station_id'])) {
            $query->where('station_id', $filters['station_id']);
        }

        $updates = $query->with('station')->get();
        if ($updates->isEmpty()) return 0;

        // Group by station to avoid redundant notifications
        $updatesByStation = $updates->groupBy('station_id');

        foreach ($updatesByStation as $stationId => $stationUpdates) {
            // Mark all as verified
            StationUpdate::whereIn('id', $stationUpdates->pluck('id'))->update(['is_verified' => true]);

            // Apply the LATEST one's status
            $latest = $stationUpdates->sortByDesc('created_at')->first();
            $station = $latest->station;

            $this->applyStatusDirectly($station, null, [
                'petrol'          => $latest->petrol,
                'petrol_normal'   => $latest->petrol_normal,
                'petrol_improved' => $latest->petrol_improved,
                'petrol_super'    => $latest->petrol_super,
                'diesel'          => $latest->diesel,
                'kerosene'        => $latest->kerosene,
                'gas'             => $latest->gas,
                'congestion'      => $latest->congestion,
            ], 'verified_users');

            $station->load('status');
            broadcast(new StationUpdated($station))->toOthers();
            $this->notifyAllUsers($station);
        }

        return $updates->count();
    }

    /**
     * Interact with a user update (confirm or dispute).
     */
    public function interactWithUpdate(StationUpdate $update, User $user, string $type): array
    {
        $cooldown = (int) AppSetting::get('interaction_cooldown_minutes', 60);

        // 1. Check if user already interacted with THIS exact update
        $existing = UpdateInteraction::where('user_id', $user->id)
            ->where('station_update_id', $update->id)
            ->exists();

        if ($existing) {
            return ['success' => false, 'message' => 'لقد قمت بتقييم هذا التحديث مسبقاً.'];
        }

        // 2. Check if user updated this station recently
        $hasRecentUpdate = StationUpdate::where('station_id', $update->station_id)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes($cooldown))
            ->exists();

        if ($hasRecentUpdate) {
            return ['success' => false, 'message' => 'لا يمكنك التقييم لأنك قمت بتحديث هذه المحطة مؤخراً.'];
        }

        // 3. Check if user interacted with ANY update of this station recently
        $hasRecentInteraction = UpdateInteraction::where('user_id', $user->id)
            ->whereHas('stationUpdate', function($q) use ($update) {
                $q->where('station_id', $update->station_id);
            })
            ->where('created_at', '>=', now()->subMinutes($cooldown))
            ->exists();

        if ($hasRecentInteraction) {
            return ['success' => false, 'message' => "لقد قمت بتقييم هذه المحطة مؤخراً. الرجاء الانتظار لمدة $cooldown دقيقة قبل التقييم مرة أخرى."];
        }

        // 4. Record interaction
        $station = $update->station;
        $currentStatus = $station->status;

        // Logic: The user clicks "Correct/Wrong" based on what they see in the UI (StationStatus).
        // If the pending update is trying to CHANGE the status, and the user clicks "Correct" (agreeing with status quo),
        // then the user is actually DISPUTING the pending update.
        
        $updateMatchesStatus = true;
        $fields = ['petrol_normal', 'petrol_improved', 'petrol_super', 'diesel', 'kerosene', 'gas'];
        
        foreach ($fields as $field) {
            // If the update modified this field and it's different from current status
            if ($update->{$field} !== null && $currentStatus && $update->{$field} !== $currentStatus->{$field}) {
                $updateMatchesStatus = false;
                break;
            }
        }

        $realType = $type;
        if ($type === 'confirm') {
            // User says UI is "Correct". 
            // If update matches UI -> confirm the update.
            // If update differs from UI -> dispute the update (user says the change is wrong).
            $realType = $updateMatchesStatus ? 'confirm' : 'dispute';
        } else {
            // User says UI is "Wrong".
            // If update matches UI -> dispute the update (user says status quo is wrong).
            // If update differs from UI -> confirm the update (user agrees that a change is needed).
            $realType = $updateMatchesStatus ? 'dispute' : 'confirm';
        }

        UpdateInteraction::create([
            'user_id' => $user->id,
            'station_update_id' => $update->id,
            'type' => $realType,
        ]);

        // 3. Process interaction
        if ($realType === 'confirm') {
            $update = $this->updateRepository->incrementConfirmation($update);
        } elseif ($realType === 'dispute') {
            $update = $this->updateRepository->incrementDispute($update);
        }

        // 4. If status changed or verified, notify users
        $station = $update->station;
        $this->applyBestAvailableStatus($station);
        $station->load('status');
        broadcast(new StationUpdated($station))->toOthers();
        
        // Only trigger push notification if it became verified just now
        if ($type === 'confirm' && $update->is_verified) {
             $this->notifyAllUsers($station);
        }

        return ['success' => true, 'update' => $update];
    }

    /**
     * Confirm a user update (legacy direct call). When threshold reached → apply status + notify all.
     */
    public function confirmUpdate(StationUpdate $update): StationUpdate
    {
        $update = $this->updateRepository->incrementConfirmation($update);

        if ($update->is_verified) {
            $station = $update->station;
            $this->applyBestAvailableStatus($station);
            $station->load('status');
            broadcast(new StationUpdated($station))->toOthers();
            $this->notifyAllUsers($station);
        }

        return $update;
    }

    public function applyBestAvailableStatus(Station $station): void
    {
        // Get all updates that are active, ordered by newest first
        $activeUpdates = StationUpdate::where('station_id', $station->id)
            ->active()
            ->orderByDesc('created_at')
            ->get();

        if ($activeUpdates->isEmpty()) return;

        $threshold = (int) AppSetting::get('verification_threshold', 3);
        $fields = ['petrol', 'petrol_normal', 'petrol_improved', 'petrol_super', 'diesel', 'kerosene', 'gas', 'congestion'];
        
        $finalStatus = [];
        $anyVerified = false;
        $hasAdminWin = false;

        $currentStatus = $station->status;

        foreach ($fields as $field) {
            // Group votes by value across ALL active updates
            $votes = [];
            foreach ($activeUpdates as $u) {
                $val = $u->{$field};
                if ($val === null) continue;

                // Admin updates are considered "Instantly Verified" and carry high weight
                $weight = $u->is_admin_update ? 1000 : (1 + $u->confirmation_count);
                $votes[$val] = ($votes[$val] ?? 0) + $weight;
            }

            // Find the best value that reached the threshold
            $bestVal = null;
            $maxWeight = 0;
            
            foreach ($votes as $val => $weight) {
                if ($weight >= $threshold && $weight > $maxWeight) {
                    $bestVal = $val;
                    $maxWeight = $weight;
                }
            }

            if ($bestVal) {
                $finalStatus[$field] = $bestVal;
                $anyVerified = true;
                
                if ($maxWeight >= 1000) {
                    $hasAdminWin = true;
                } else if ($maxWeight >= $threshold) {
                    // Auto-verify any report that contributed to this winning consensus
                    foreach ($activeUpdates as $u) {
                        if ($u->{$field} === $bestVal && !$u->is_admin_update && !$u->is_verified) {
                            $u->update(['is_verified' => true]);
                        }
                    }
                }
            } else {
                // If no value reached threshold, maintain the current status for this field
                if ($currentStatus && $currentStatus->{$field} !== null) {
                    $finalStatus[$field] = $currentStatus->{$field};
                }
            }
        }

        // Logic sync: If specific petrol types are updated, ensure generic 'petrol' reflects them
        if (isset($finalStatus['petrol_normal']) || isset($finalStatus['petrol_improved']) || isset($finalStatus['petrol_super'])) {
            $pNormal = $finalStatus['petrol_normal'] ?? 'unavailable';
            $pImproved = $finalStatus['petrol_improved'] ?? 'unavailable';
            $pSuper = $finalStatus['petrol_super'] ?? 'unavailable';
            
            if ($pNormal === 'available' || $pImproved === 'available' || $pSuper === 'available') {
                $finalStatus['petrol'] = 'available';
            } else {
                $finalStatus['petrol'] = 'unavailable';
            }
        }

        if ($anyVerified) {
            $source = $hasAdminWin ? 'admin' : 'verified_users';
            $this->applyStatusDirectly($station, null, $finalStatus, $source);
        }
    }

    private function applyStatusDirectly(Station $station, ?User $updatedBy, array $data, string $source): void
    {
        $fillable = array_filter([
            'petrol'          => $data['petrol'] ?? null,
            'petrol_normal'   => $data['petrol_normal'] ?? null,
            'petrol_improved' => $data['petrol_improved'] ?? null,
            'petrol_super'    => $data['petrol_super'] ?? null,
            'diesel'          => $data['diesel'] ?? null,
            'kerosene'        => $data['kerosene'] ?? null,
            'gas'             => $data['gas'] ?? null,
            'congestion'      => $data['congestion'] ?? null,
            'source'          => $source,
            'last_updated_at' => now(),
            'updated_by'      => $updatedBy?->id,
        ], fn($v) => $v !== null);

        StationStatus::updateOrCreate(
            ['station_id' => $station->id],
            $fillable,
        );
    }

    /**
     * Notify ALL app users (including guests) via FCM topic when station status changes.
     */
    private function notifyAllUsers(Station $station): void
    {
        try {
            $status  = $station->status;
            $overall = $status?->overall_status ?? 'unavailable';

            $label = match($overall) {
                'available'   => 'متوفر ✅',
                'limited'     => 'محدود ⚠️',
                'unavailable' => 'غير متوفر ❌',
                default       => $overall,
            };

            $name  = $station->name_ar ?? $station->name;
            $title = "تحديث محطة: {$name}";
            $body  = "حالة الوقود: {$label}";

            (new FcmV1Service())->sendToTopic('all_users', $title, $body, null, [
                'type'       => 'station_update',
                'station_id' => (string) $station->id,
                'overall'    => $overall,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[UpdateService] notifyAllUsers failed', ['error' => $e->getMessage()]);
        }
    }
}
