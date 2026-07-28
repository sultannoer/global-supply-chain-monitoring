<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $role = $credentials['role'];
        unset($credentials['role']);

        if (! Auth::attempt($credentials + ['role' => $role], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email, kata sandi, atau jenis akun tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        // The selected role is authoritative. Do not let an earlier guest URL
        // (for example /) override the Admin dashboard destination.
        return redirect()->route($role === 'admin' ? 'admin.dashboard' : 'ports.index');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        return redirect()->route('login')->with('status', 'Akun User berhasil dibuat. Silakan masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar dari GeoPort Analytics.');
    }
}
