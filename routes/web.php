<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ShopController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\User\PublicProductController;
use App\Http\Controllers\User\PublicCategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini Anda bisa mendaftarkan rute web untuk aplikasi Anda. Rute-rute
| ini dimuat oleh RouteServiceProvider dan semuanya akan
| ditetapkan ke grup middleware "web".
|
*/

// Rute default Laravel
Route::get('/', function () {
    return view('welcome');
});



/*
|--------------------------------------------------------------------------
| Rute Autentikasi (Publik)
|--------------------------------------------------------------------------
| Rute untuk proses login, register, dan autentikasi pihak ketiga.
*/
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/login-admin', 'loginAdmin');
    Route::post('/register', 'register');
});

Route::controller(SocialiteController::class)->prefix('auth/google')->group(function () {
    Route::get('/redirect', 'redirect');
    Route::get('/callback', 'callback');
});


// API PUBLIK UNTUK SPA
Route::get('/products/featured', [PublicProductController::class, 'featured']);
Route::get('/products/{product:slug}', [PublicProductController::class, 'show']);
Route::get('/products/{product:slug}/related', [PublicProductController::class, 'related']);

Route::get('/categories/top', [PublicCategoryController::class, 'top']);
// --- TAMBAHKAN DUA ROUTE DI BAWAH INI ---
// Route untuk mendapatkan semua produk dengan filter & paginasi
Route::get('/shop/products', [ShopController::class, 'getProducts']);

// Route untuk mendapatkan data master untuk filter (kategori, warna, dll)
Route::get('/shop/filters', [ShopController::class, 'getFilterMasterData']); // <-- TAMBAHKAN ROUTE INI

/*
|--------------------------------------------------------------------------
| Rute Terproteksi (Memerlukan Login)
|--------------------------------------------------------------------------
| Rute untuk pengguna yang sudah login (baik customer maupun admin).
*/
Route::middleware(['auth:sanctum'])->group(function () {

    // Mengambil data pengguna yang sedang login & proses logout
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint untuk validasi sesi oleh middleware Next.js
    Route::get('/check', [AuthController::class, 'check']);

    // Rute untuk keranjang belanja customer
    Route::apiResource('cart', CartController::class);
});


/*
|--------------------------------------------------------------------------
| Rute Panel Admin
|--------------------------------------------------------------------------
| Semua rute di sini dilindungi oleh middleware 'auth:sanctum' dan
| memerlukan peran 'admin' atau 'super-admin'.
*/
Route::middleware(['auth:sanctum', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Produk
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('sizes', SizeController::class);
    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('vouchers', VoucherController::class);

    // Anda bisa menambahkan rute admin lainnya di sini
});
