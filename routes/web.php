<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/capture/{orderId}', [OrderController::class, 'capture'])->name('orders.capture');
Route::get('/orders/cancel/{orderId}', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::get('/orders/process-payment/{orderId}', [OrderController::class, 'processPayment'])->name('orders.process-payment');
Route::get('/orders/success', fn() => view('orders.success'))->name('orders.success');
Route::get('/orders/error', fn() => view('orders.error'))->name('orders.error');
