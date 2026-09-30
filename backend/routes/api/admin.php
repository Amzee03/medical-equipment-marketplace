<?php

// routes/api/admin.php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------
// Admin routes — middleware auth:sanctum + admin
// -----------------------------------------------------------------------
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

    // -------------------------------------------------------------------
    // Category management
    // -------------------------------------------------------------------
    Route::get('/categories', [CategoryController::class, 'adminIndex']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::patch('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // -------------------------------------------------------------------
    // Product management
    // -------------------------------------------------------------------
    Route::get('/products', [ProductController::class, 'adminIndex']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::patch('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // -------------------------------------------------------------------
    // Product images management
    // -------------------------------------------------------------------
    Route::post('/products/{id}/images', [ProductImageController::class, 'store']);
    Route::delete('/products/{productId}/images/{imageId}', [ProductImageController::class, 'destroy']);
    Route::patch('/products/{productId}/images/{imageId}/primary', [ProductImageController::class, 'setPrimary']);

    // -------------------------------------------------------------------
    // Stock management (purchase)
    // -------------------------------------------------------------------
    Route::patch('/products/{id}/stock', [StockController::class, 'updateStock']);

    // -------------------------------------------------------------------
    // Equipment unit management (rental)
    // -------------------------------------------------------------------
    Route::get('/products/{id}/equipment-units', [StockController::class, 'listUnits']);
    Route::post('/products/{id}/equipment-units', [StockController::class, 'addUnit']);
    Route::patch('/equipment-units/{id}', [StockController::class, 'updateUnit']);
    Route::delete('/equipment-units/{id}', [StockController::class, 'retireUnit']);
    // -------------------------------------------------------------------
    // Order management
    // -------------------------------------------------------------------
    Route::get('/orders', [\App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/review-cancellation', [\App\Http\Controllers\Api\Admin\OrderController::class, 'reviewCancellation']);

    // -------------------------------------------------------------------
    // Refund management
    // -------------------------------------------------------------------
    Route::get('/refunds', [\App\Http\Controllers\Api\Admin\RefundController::class, 'index']);
    Route::post('/orders/{id}/refunds', [\App\Http\Controllers\Api\Admin\RefundController::class, 'store']);
    Route::patch('/refunds/{id}/status', [\App\Http\Controllers\Api\Admin\RefundController::class, 'updateStatus']);
});

