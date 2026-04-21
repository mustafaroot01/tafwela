<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $request->email)
            ->where('is_admin', true)
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password ?? '')) {
            return back()->withErrors(['email' => 'البيانات المدخلة غير صحيحة.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
