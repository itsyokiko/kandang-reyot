<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login mobile.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Autentikasi / Registrasi via Firebase Google Auth.
     */
    public function firebaseLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'uid' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:2000',
        ]);

        $user = User::where('email', $validated['email'])
            ->orWhere('firebase_uid', $validated['uid'])
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => $validated['name'] ?: 'Peternak',
                'email' => $validated['email'],
                'firebase_uid' => $validated['uid'],
                'avatar' => $validated['avatar'] ?? null,
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'name' => $validated['name'] ?: $user->name,
                'firebase_uid' => $validated['uid'],
                'avatar' => $validated['avatar'] ?: $user->avatar,
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        // Jika ada data lama tanpa user_id dan hanya ada 1 user, kaitkan ke user ini
        if (User::count() === 1) {
            \App\Models\Kambing::withoutGlobalScope('user_isolation')->whereNull('user_id')->update(['user_id' => $user->id]);
            \App\Models\Kelahiran::withoutGlobalScope('user_isolation')->whereNull('user_id')->update(['user_id' => $user->id]);
            \App\Models\Perkawinan::withoutGlobalScope('user_isolation')->whereNull('user_id')->update(['user_id' => $user->id]);
            \App\Models\RiwayatSakit::withoutGlobalScope('user_isolation')->whereNull('user_id')->update(['user_id' => $user->id]);
            \App\Models\PengingatPerawatan::withoutGlobalScope('user_isolation')->whereNull('user_id')->update(['user_id' => $user->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Google berhasil.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
            'redirect' => route('dashboard'),
        ]);
    }

    /**
     * Logout pengguna dari sesi aplikasi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari akun.');
    }
}
