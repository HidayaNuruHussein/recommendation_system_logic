<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\OrderController;

/*
|--------------------------------------------------------------------------
| Shop Routes
|--------------------------------------------------------------------------
*/

// Cart routes - Protected with auth and role middleware
Route::prefix('cart')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add', [CartController::class, 'add'])->middleware('verified')->name('cart.add');
    Route::patch('/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// Cart API routes (for header updates, etc.) - no auth required for count
Route::prefix('api/cart')->group(function () {
    Route::get('/count', [CartController::class, 'getCartCount'])->name('api.cart.count');
});

// ✅ Checkout routes - FIXED: Use consistent parameter names
Route::prefix('checkout')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/success/{orderId}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/failed/{orderId}', [CheckoutController::class, 'failed'])->name('checkout.failed');
    Route::post('/retry/{orderId}', [CheckoutController::class, 'retry'])->name('checkout.retry');
});

// Order routes
Route::prefix('orders')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});