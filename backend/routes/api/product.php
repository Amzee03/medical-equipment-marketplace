<?php

// routes/api/product.php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------
// Public routes — tidak butuh token
// -----------------------------------------------------------------------

/** GET /api/categories — Daftar kategori aktif */
Route::get('/categories', [CategoryController::class, 'index']);

/** GET /api/products — Daftar produk aktif dengan pagination + filter + search */
Route::get('/products', [ProductController::class, 'index']);

/** GET /api/products/{slug} — Detail produk by slug */
Route::get('/products/{slug}', [ProductController::class, 'show']);

/** GET /api/products/{id}/availability — Cek ketersediaan rental */
Route::get('/products/{id}/availability', [ProductController::class, 'checkAvailability']);

