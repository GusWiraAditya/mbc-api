<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



// // Rute ini akan mengembalikan status 200 jika user login, dan 401 jika tidak.
// Route::middleware('auth:sanctum')->get('/auth/check', function (Request $request) {
//     return response()->json(['authenticated' => true]);
// });
