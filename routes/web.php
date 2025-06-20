<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/user', [AuthController::class, 'user']);
Route::post('/logout', [AuthController::class, 'logout']);
// Route::apiResource('kategori', KategoriController::class);
