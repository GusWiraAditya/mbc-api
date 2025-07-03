<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception; // Import kelas Exception

class SocialiteController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman autentikasi Google.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        // Mengarahkan pengguna ke provider OAuth (Google)
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback dari Google setelah pengguna memberikan izin.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            // Mengambil data pengguna dari Google
            $googleUser = Socialite::driver('google')->user();

            // Logika "Cari atau Buat" Pengguna yang lebih cerdas.
            // Pertama, kita cek apakah ada pengguna dengan email yang sama.
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Jika pengguna sudah ada, kita hanya perlu memperbarui google_id mereka
                // jika belum ada, untuk "menghubungkan" akun.
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'profile_picture' => $user->profile_picture ?? $googleUser->getAvatar(), // Update avatar jika belum ada
                ]);
            } else {
                // Jika pengguna sama sekali tidak ada, kita buat akun baru.
                $user = User::create([
                    'google_id'       => $googleUser->getId(),
                    'name'            => $googleUser->getName(),
                    'email'           => $googleUser->getEmail(),
                    'profile_picture' => $googleUser->getAvatar(),
                    'password'        => Hash::make(uniqid()), // Buat password acak karena tidak diperlukan
                ]);

                // Beri peran 'customer' untuk setiap pengguna baru yang mendaftar via Google.
                $user->assignRole('customer');
            }

            // Loginkan pengguna yang sudah ditemukan atau yang baru dibuat.
            Auth::login($user);

            // Arahkan kembali ke halaman utama frontend dengan sinyal sukses.
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/?login_success=google');
        } catch (Exception $e) {
            // Jika terjadi error (misalnya, pengguna menolak izin),
            // arahkan kembali ke halaman login dengan pesan error.
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=google_login_failed');
        }
    }
}
