<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage; // <-- Impor Storage
use Illuminate\Support\Str;
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
            DB::transaction(function () {
                $googleUser = Socialite::driver('google')->user();
                $user = User::where('email', $googleUser->getEmail())->first();

                // Cek apakah pengguna sudah punya gambar profil. Jika tidak, unduh dari Google.
                $avatarPath = null;
                if (!$user || !$user->profile_picture) {
                    try {
                        $avatarContents = Http::get($googleUser->getAvatar())->body();
                        if ($avatarContents) {
                            $avatarPath = 'profile-pictures/' . Str::random(40) . '.jpg';
                            Storage::disk('public')->put($avatarPath, $avatarContents);
                        }
                    } catch (Exception $e) {
                        // Jika download gagal, tidak apa-apa. Lanjutkan tanpa gambar.
                        $avatarPath = null;
                    }
                }

                if ($user) {
                    // Update pengguna yang ada
                    $user->update([
                        'google_id' => $user->google_id ?? $googleUser->getId(),
                        // Hanya update gambar jika pengguna belum punya DAN download berhasil
                        'profile_picture' => $user->profile_picture ?? $avatarPath,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                         // <-- TAMBAHKAN BARIS INI
                    ]);
                } else {
                    // Buat pengguna baru
                    $user = User::create([
                        'google_id'       => $googleUser->getId(),
                        'name'            => $googleUser->getName(),
                        'email'           => $googleUser->getEmail(),
                        'profile_picture' => $avatarPath, // Simpan path lokal
                        'password'        => Hash::make(uniqid()),
                        'email_verified_at' => now(), // <-- TAMBAHKAN BARIS INI
                    ]);
                    $user->assignRole('customer');
                }

                Auth::login($user);
            });

            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/?login_success=google');
        } catch (Exception $e) {
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=google_login_failed');
        }
    }
}
