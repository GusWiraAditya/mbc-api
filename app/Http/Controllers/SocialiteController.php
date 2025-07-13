<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class SocialiteController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman autentikasi Google.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(Request $request)
    {
        // Simpan parameter redirect di session jika ada
        if ($request->has('redirect')) {
            session(['google_redirect_url' => $request->get('redirect')]);
        }

        // Mengarahkan pengguna ke provider OAuth (Google)
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback dari Google setelah pengguna memberikan izin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
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
                    ]);
                } else {
                    // Buat pengguna baru
                    $user = User::create([
                        'google_id'       => $googleUser->getId(),
                        'name'            => $googleUser->getName(),
                        'email'           => $googleUser->getEmail(),
                        'profile_picture' => $avatarPath, // Simpan path lokal
                        'password'        => Hash::make(uniqid()),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('customer');
                }

                Auth::login($user);
            });

            // Ambil URL redirect dari session
            $redirectUrl = session('google_redirect_url', '/');

            // Hapus dari session setelah digunakan
            session()->forget('google_redirect_url');

            // Validasi redirect URL untuk keamanan
            $redirectUrl = $this->validateRedirectUrl($redirectUrl);

            if ($redirectUrl === '/') {
                // Redirect ke homepage
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/?login_success=google&merge_cart=true');
            } else {
                // Cek apakah $redirectUrl sudah punya query string sendiri
                if (str_contains($redirectUrl, '?')) {
                    // Jika sudah, tambahkan dengan &
                    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . $redirectUrl . '&login_success=google&merge_cart=true');
                } else {
                    // Jika belum, tambahkan dengan ?
                    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . $redirectUrl . '?login_success=google&merge_cart=true');
                }
            }
        } catch (Exception $e) {
            // Jika ada error, tetap redirect ke login dengan parameter redirect jika ada
            $redirectUrl = session('google_redirect_url', '');
            session()->forget('google_redirect_url');

            $errorUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=google_login_failed';
            if ($redirectUrl && $redirectUrl !== '/') {
                $errorUrl .= '&redirect=' . urlencode($redirectUrl);
            }

            return redirect($errorUrl);
        }
    }

    /**
     * Validasi redirect URL untuk keamanan.
     * Hanya mengizinkan redirect ke path internal aplikasi.
     *
     * @param string $url
     * @return string
     */
    private function validateRedirectUrl(string $url): string
    {
        // Daftar path yang diizinkan untuk redirect
        $allowedPaths = [
            '/checkout',
            '/profile',
            '/orders',
            '/cart',
            '/collections',
            '/products',
            '/contact',
            '/about',

            // Tambahkan path lain yang diizinkan
        ];

        // Jika URL kosong atau hanya '/', return '/'
        if (empty($url) || $url === '/') {
            return '/';
        }

        // Hapus domain jika ada (untuk keamanan)
        $path = parse_url($url, PHP_URL_PATH);

        // Jika parse_url gagal, return '/'
        if ($path === false || $path === null) {
            return '/';
        }

        // Cek apakah path diizinkan
        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($path, $allowedPath)) {
                return $path;
            }
        }

        // Jika tidak diizinkan, return '/'
        return '/';
    }
}
