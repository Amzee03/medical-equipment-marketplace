<?php

use App\Http\Controllers\Api\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('checkout', [CheckoutController::class, 'store']);
});
