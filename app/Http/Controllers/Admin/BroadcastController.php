<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\Station;
use App\Models\User;
use App\Services\FcmV1Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    public function index(): View
    {
        $recent   = PushNotification::with(['user', 'station'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stations = Station::active()->orderBy('name_ar')->get(['id', 'name', 'name_ar']);

        return view('admin.notifications.index', compact('recent', 'stations'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'title'     => 'required|string|max:100',
            'body'      => 'required|string|max:500',
            'image_url' => 'nullable|url|max:500',
            'target'    => 'required|in:all,station,user',
            'station_id'=> 'required_if:target,station|nullable|exists:stations,id',
            'phone'     => 'required_if:target,user|nullable|string',
        ]);

        $users = collect();

        if ($request->target === 'all') {
            $users = User::whereNotNull('fcm_token')
                ->where('is_banned', false)
                ->get(['id', 'fcm_token']);
        } elseif ($request->target === 'station') {
            $station = Station::findOrFail($request->station_id);
            $users = $station->favoritedBy()
                ->whereNotNull('fcm_token')
                ->where('is_banned', false)
                ->get(['users.id', 'fcm_token']);
        } elseif ($request->target === 'user') {
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return back()->with('error', 'المستخدم غير موجود');
            }
            $users = collect([$user]);
        }

        $fcm = new FcmV1Service();

        if ($request->target === 'all') {
            // Send to FCM topic → reaches ALL users including guests
            $fcm->sendToTopic('all_users', $request->title, $request->body, $request->image_url ?: null);

            // Save ONE notification record representing the global broadcast
            PushNotification::create([
                'user_id'    => null, // Null means global broadcast
                'station_id' => null,
                'title'      => $request->title,
                'body'       => $request->body,
                'image_url'  => $request->image_url ?: null,
                'type'       => 'admin_broadcast',
                'data'       => ['target' => 'all'],
                'is_read'    => false,
            ]);

            return back()->with('success', "تم إرسال الإشعار لجميع المستخدمين عبر FCM Topic وحفظه في السجل");
        }

        // Targeted send (station / user) — individual tokens
        $sent = 0;
        foreach ($users as $user) {
            PushNotification::create([
                'user_id'    => $user->id,
                'station_id' => $request->target === 'station' ? $request->station_id : null,
                'title'      => $request->title,
                'body'       => $request->body,
                'image_url'  => $request->image_url ?: null,
                'type'       => 'admin_broadcast',
                'data'       => ['target' => $request->target],
                'is_read'    => false,
            ]);

            if ($user->fcm_token) {
                $fcm->send($user->fcm_token, $request->title, $request->body, $request->image_url ?: null);
            }

            $sent++;
        }

        return back()->with('success', "تم إرسال الإشعار إلى {$sent} مستخدم");
    }

    public function destroy(PushNotification $notification): RedirectResponse
    {
        $notification->delete();
        return back()->with('success', 'تم حذف الإشعار');
    }
}
