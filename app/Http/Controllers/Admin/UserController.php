<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function index(Request $request): View
    {
        $users = $this->userRepository->allPaginated($request->only(['search', 'is_banned']));

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['stationUpdates.station']);
        return view('admin.users.show', compact('user'));
    }

    public function ban(User $user): RedirectResponse
    {
        $this->userRepository->ban($user);

        return back()->with('success', "User {$user->phone} has been banned.");
    }

    public function unban(User $user): RedirectResponse
    {
        $this->userRepository->unban($user);

        return back()->with('success', "User {$user->phone} has been unbanned.");
    }

    public function toggleTrusted(User $user): RedirectResponse
    {
        $user->is_trusted = !$user->is_trusted;
        $user->save();

        $status = $user->is_trusted ? 'موثق (Trusted)' : 'عادي';
        return back()->with('success', "تم تغيير حالة المستخدم {$user->phone} إلى: {$status}");
    }
}
