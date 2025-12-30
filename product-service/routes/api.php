<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
|
| REST endpoints for Product microservice following specification v1.2.0
| Base path: /api/products
|
| Route Model Binding:
| - {product} automatically resolves to Product model instance
| - Returns 404 if product not found (handled by Laravel)
| - Validates ID format automatically
|
*/

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('products', ProductController::class);

    Route::get('/check-auth', function (Request $request) {
        return [
            'user' => $request->user(),
            'token' => $request->user()?->currentAccessToken(),
        ];
    });
});

/*
// GET /api/products - List all products with pagination and filters
Route::get('products', [ProductController::class, 'index']);

// POST /api/products - Create new product
Route::post('products', [ProductController::class, 'store']);

// GET /api/products/{product} - Get product by ID (Route Model Binding)
Route::get('products/{product}', [ProductController::class, 'show']);

// PUT /api/products/{product} - Update product (Route Model Binding)
Route::put('products/{product}', [ProductController::class, 'update']);

// DELETE /api/products/{product} - Delete product (Route Model Binding)
Route::delete('products/{product}', [ProductController::class, 'destroy']);
*/
/*Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
*/