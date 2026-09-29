<?php

use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
});
