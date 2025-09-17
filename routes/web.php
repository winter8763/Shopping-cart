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

Route::get('/', function () {
    return view('welcome');
});

// 使用者相關路由
Route::get('register', [UserController::class, 'showRegisterForm']);
Route::post('register', [UserController::class, 'register']);
Route::get('login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout']);
Route::get('profile', [UserController::class, 'profile'])->middleware(['auth', 'verified']);
Route::post('profile', [UserController::class, 'updateProfile'])->middleware(['auth', 'verified']);

// 若需要重新寄送驗證信（登入狀態下）
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', '驗證信已重新寄出。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
Route::get('verification/notice', function () {
    return view('auth.verify_notice');
})->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    // 檢查 hash 是否對應該使用者的 email（安全檢查）
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    // 若尚未驗證就標記為已驗證並發事件
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    // 驗證成功後導回登入頁（或導到你想要的頁面）
    return redirect('/login?verified=1');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// 商品相關路由
Route::resource('products', ProductController::class);
Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});

// 購物車相關路由
Route::get('cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// 訂單相關路由
Route::middleware('auth')->group(function () {
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store'); // 建立訂單（checkout）
});

// 願望清單相關路由
Route::middleware('auth')->group(function () {
    // 願望清單
    Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::post('wishlist/{item}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::delete('wishlist/{item}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});
