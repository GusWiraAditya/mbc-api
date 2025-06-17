<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menangani permintaan login dari pengguna.
     */
    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Berikan role customer ke user baru
        $user->assignRole('customer');

        // Login user yang baru dibuat
        Auth::login($user);

        // Regenerate session setelah login
        $request->session()->regenerate();

        // Kirim response
        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user,
            'roles' => $user->getRoleNames(),
        ], 201); // 201 Created
    }

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

        // Kirim response yang berisi data user DAN roles-nya
        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames() // mengambil nama role
        ]);
    }

   public function loginAdmin(Request $request)
    {
        // 1. Validasi input
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        // 2. Coba auth
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok.'],
            ]);
        }

        // 3. Regenerate session untuk mencegah fixation
        $request->session()->regenerate();

        $user = $request->user();

        // 4. Cek role
        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            // logout user yang sebenarnya sudah login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // beri error 403
            return response()->json([
                'message' => 'Akun tidak ditemukan'
            ], 403);
        }

        // 5. Kirim response user + roles
        return response()->json([
            'user'  => $user->only(['id','name','email']),
            'roles' => $user->getRoleNames(), // e.g. ['admin']
        ], 200);
    }
    /**
     * Mengambil data user yang sedang terotentikasi.
     */
    public function user(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        // Kirim  roles saat mengambil data user
        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
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
