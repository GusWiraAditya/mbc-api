<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menangani permintaan login dari pengguna.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        // ---- PERUBAHAN DI SINI ----
        // Kirim response yang berisi data user DAN roles-nya
        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames() // Helper dari Spatie untuk mengambil nama role
        ]);
        // ---- AKHIR PERUBAHAN ----
    }

    /**
     * Mengambil data user yang sedang terotentikasi.
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        // ---- PERUBAHAN DI SINI ----
        // Kirim juga roles saat mengambil data user
        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
        // ---- AKHIR PERUBAHAN ----
    }

    /**
     * Menangani permintaan logout.
     * (Tidak ada perubahan di sini)
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Berhasil logout']);
    }
}