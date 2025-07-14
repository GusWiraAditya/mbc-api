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
use App\Http\Controllers\User\MidtransController;
use App\Http\Controllers\User\ShippingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\User\PublicProductController;
use App\Http\Controllers\User\PublicCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

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

Route::post('/midtrans/notification', [MidtransController::class, 'notificationHandler']);

/*
|--------------------------------------------------------------------------
| Rute Terproteksi (Memerlukan Login)
|--------------------------------------------------------------------------
| Rute untuk pengguna yang sudah login (baik customer maupun admin).
*/
Route::middleware(['auth:sanctum'])->group(function () {

   // Auth & Profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check', [AuthController::class, 'check']);
    Route::put('/user/profile', [ProfileController::class, 'update']);

    // Address
    Route::post('addresses/{address}/set-primary', [AddressController::class, 'setPrimary']);
    Route::apiResource('addresses', AddressController::class);

    // Order
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/retry-payment', [OrderController::class, 'retryPayment'])->name('orders.retry-payment');
    Route::post('/orders/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery']);
    // Location (Hanya untuk data geografis)
    Route::controller(LocationController::class)->prefix('location')->group(function () {
        Route::get('/provinces', 'getProvinces');
        Route::get('/cities/{provinceId}', 'getCities');
        Route::get('/districts/{cityId}', 'getDistricts');
        Route::get('/subdistricts/{districtId}', 'getSubdistricts');
    });
    
    // Shipping (Hanya untuk kalkulasi biaya)
    Route::post('/shipping/cost', [ShippingController::class, 'calculateCost']);

    // Vouchers (di dalam CartController)
    Route::controller(CartController::class)->prefix('vouchers')->group(function () {
        Route::post('/apply', 'applyVoucher');
        Route::post('/remove', 'removeVoucher');
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

    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    // Manajemen Produk
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('sizes', SizeController::class);
    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('products', ProductController::class);
    Route::resource('vouchers', VoucherController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::controller(AdminOrderController::class)->prefix('orders')->name('orders.')->group(function () {
        // Halaman untuk menampilkan semua pesanan
        Route::get('/', 'index')->name('index');
        
        // Halaman untuk menampilkan detail satu pesanan
        Route::get('/{order}', 'show')->name('show');
        
        // Aksi untuk mengubah status pesanan (akan kita buat nanti)
        Route::patch('/{order}/status', 'updateStatus')->name('updateStatus');
        
        // Aksi untuk menambah nomor resi (akan kita buat nanti)
        Route::post('/{order}/tracking', 'addTrackingNumber')->name('addTracking');
    });
});

Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::post('/settings', [SettingController::class, 'update']);
});
