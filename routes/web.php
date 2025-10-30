<?php

use Illuminate\Support\Facades\Route;

// === IMPORT CONTROLLERS ===
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransaksiPenjualanController;




// === DEFAULT ROUTE ===
// Arahkan root (/) langsung ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// === AUTHENTICATION ROUTES ===
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/send-email/{to}/{id}', [\App\Http\Controllers\TransaksiPenjualanController::class, 'sendEmail']);
// === ROUTE YANG DILINDUNGI LOGIN (AUTH MIDDLEWARE) ===
Route::middleware(['auth'])->group(function () {

    // Products
    Route::resource('products', ProductController::class);

    // Categories
    Route::resource('categories', ProductCategoryController::class);

    // Supplier 
    Route::resource('suppliers', SupplierController::class);

    // Transaksi Penjualan
    Route::resource('transaksi', TransaksiPenjualanController::class);

});
