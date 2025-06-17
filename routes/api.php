<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\CategoryController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:admin|super_admin'])->prefix('admin')->group(function () {
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('cart', CartController::class);
    Route::apiResource('product', ProductController::class);
    Route::apiResource('voucher', VoucherController::class);
});


