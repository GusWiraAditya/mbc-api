<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ShopController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
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



Route::get('/products/featured', [PublicProductController::class, 'featured']);
Route::get('/products/{product:slug}', [PublicProductController::class, 'show']);
Route::get('/products/{product:slug}/related', [PublicProductController::class, 'related']);

Route::get('/categories/top', [PublicCategoryController::class, 'top']);
// Route untuk mendapatkan semua produk dengan filter & paginasi
Route::get('/shop/products', [ShopController::class, 'getProducts']);
// Route untuk mendapatkan data master untuk filter (kategori, warna, dll)
Route::get('/shop/filters', [ShopController::class, 'getFilterMasterData']); // <-- TAMBAHKAN ROUTE INI
Route::get('/admin/settings', [SettingController::class, 'index']);



Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    // [GET] Mengambil state keranjang saat ini (items, summary, vouchers)
    Route::get('/', 'index')->name('index');

    // [POST] Menambahkan item baru ke keranjang
    Route::post('/add', 'add')->name('add');

    // [POST] Menggabungkan keranjang guest dengan keranjang user setelah login
    Route::post('/merge', 'merge')->name('merge');

    // [PUT] Mengubah kuantitas satu item di keranjang
    // Kita menggunakan {cart}, Laravel akan otomatis mencari Cart item dengan ID tersebut
    Route::put('/update/{cart}', 'update')->name('update');

    // [POST] Menghapus satu atau lebih item dari keranjang
    Route::post('/remove', 'remove')->name('remove');

    // [POST] Mengosongkan seluruh isi keranjang
    Route::post('/clear', 'clear')->name('clear');

    // [POST] Menandai/membatalkan pilihan item untuk checkout
    Route::post('/toggle-select', 'toggleSelect')->name('toggle-select');

    // Route khusus untuk mengatur alamat utama. Harus diletakkan sebelum apiResource.
});
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
    Route::put('/user/profile', [ProfileController::class, 'update'])->name('user.profile.update');

    Route::post('addresses/{address}/set-primary', [AddressController::class, 'setPrimary'])
        ->name('addresses.set-primary');

     Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::apiResource('addresses', AddressController::class);
    Route::controller(LocationController::class)->prefix('location')->name('location.')->group(function () {
       Route::get('/provinces', 'getProvinces')->name('provinces');
    Route::get('/cities/{provinceId}', 'getCities')->name('cities');
    Route::get('/districts/{cityId}', 'getDistricts')->name('districts'); // <-- BARU
    Route::get('/subdistricts/{districtId}', 'getSubdistricts')->name('subdistricts'); // <-- BARU
    Route::post('/cost', 'calculateCost')->name('cost');
    });

    Route::controller(CartController::class)->prefix('vouchers')->name('vouchers.')->group(function () {
        // [POST] Menerapkan kode voucher ke keranjang
        Route::post('/apply', 'applyVoucher')->name('apply');

        // [POST] Menghapus voucher yang sudah diterapkan
        Route::post('/remove', 'removeVoucher')->name('remove');
    });
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
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('sizes', SizeController::class);
    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('products', ProductController::class);
    Route::resource('vouchers', VoucherController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::post('/settings', [SettingController::class, 'update']);
});
