<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/product.php';
require __DIR__.'/api/cart.php';
require __DIR__.'/api/order.php';
require __DIR__.'/api/admin.php';

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
