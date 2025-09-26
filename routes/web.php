<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\VerificationController;

Route::get('/', [ProductController::class, 'index'])->name('home');

// 使用者相關路由
Route::get('register', [UserController::class, 'showRegisterForm']);
Route::post('register', [UserController::class, 'register']);
Route::get('login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('profile', [UserController::class, 'updateProfile'])->name('profile.update');
});

// Email驗證相關路由 
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::get('verification/notice', [VerificationController::class, 'notice'])
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// 商品相關路由
Route::prefix('products')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::get('{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');


// 購物車相關路由
Route::prefix('cart')->middleware('auth')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
    Route::post('update', [CartController::class, 'update'])->name('cart.update');
    Route::post('remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('clear', [CartController::class, 'clear'])->name('cart.clear');
});

// 訂單相關路由
Route::prefix('orders')->middleware('auth')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
    Route::get('{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/', [OrderController::class, 'store'])->name('orders.store'); // 建立訂單（checkout）
});

// 願望清單相關路由
Route::prefix('wishlist')->middleware('auth')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::post('{item}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::delete('{item}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});
