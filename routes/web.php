<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(
    [
        'register' => true,
        'reset' => false,
        'verify' => false,
    ]
);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/', [ProductController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/show/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::get('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::get('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::post('/cart/check-cep', [CartController::class, 'checkCep'])->name('cart.check-cep');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/edit/{product}', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('products/update/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('products/destroy/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::resource('coupons', CouponController::class)->middleware('admin');
});

Route::post('/webhook/order', [WebhookController::class, 'handleOrderStatus'])->name('webhook.order');
