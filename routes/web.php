<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
Route::post('/register', [AuthController::class, 'register']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    // Route::post('/logout-admin', [AuthController::class, 'logoutAdmin']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('cart', CartController::class);
    // REVISI: Tambahkan rute ini di sini
    // Session validation endpoint with better error handling
        Route::get('/check', function (Request $request) {
            try {
                // Additional check to ensure user is authenticated
                if (!$request->user()) {
                    return response()->json([
                        'authenticated' => false,
                        'message' => 'User not authenticated'
                    ], 401);
                }

                return response()->json([
                    'authenticated' => true,
                    'user_id' => $request->user()->id,
                    'user' => [
                        // Pastikan Anda mengirimkan peran (roles)
                        'roles' => $request->user()->getRoleNames()
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'authenticated' => false,
                    'error' => 'Authentication check failed'
                ], 500);
            }
        });
});

Route::middleware(['auth:sanctum', 'role:admin|super-admin'])->prefix('admin')->group(function () {
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('product', ProductController::class);
    Route::apiResource('voucher', VoucherController::class);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
// Route::apiResource('kategori', KategoriController::class);
