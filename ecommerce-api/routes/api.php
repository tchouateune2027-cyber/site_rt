<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

/*
|----------------------------------------------------------
| ROUTES PUBLIQUES — Accessibles sans token
|----------------------------------------------------------
*/
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

/*
|----------------------------------------------------------
| ROUTES PROTÉGÉES — Token obligatoire
|----------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Profil utilisateur
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Panier
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

    // Commandes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Routes Admin — Créer/Modifier/Supprimer
    Route::middleware('admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
