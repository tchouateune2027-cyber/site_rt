<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

// ═══════════════════════════════════════════════
// ROUTES PUBLIQUES
// Pas besoin de token pour y accéder
// ═══════════════════════════════════════════════
Route::get('/products',             [ProductController::class,  'index']);
Route::get('/products/{product}',   [ProductController::class,  'show']);
Route::get('/services',             [ServiceController::class,  'index']);
Route::get('/services/{service}',   [ServiceController::class,  'show']);
Route::get('/categories',           [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// ═══════════════════════════════════════════════
// ROUTES PROTÉGÉES PAR TOKEN
// auth:sanctum = vérifie que le token est valide
// ═══════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // Mon profil connecté
    Route::get('/user', function (Request $request) {
        // Charge le rôle avec l'utilisateur
        return response()->json([
            'success' => true,
            'data'    => $request->user()->load('role')
        ]);
    });

    // ───────────────────────────────────────────
    // PANIER — tous les utilisateurs connectés
    // ───────────────────────────────────────────
    Route::get('/cart',               [CartController::class, 'index']);
    Route::post('/cart',              [CartController::class, 'store']);
    Route::put('/cart/{cartItem}',    [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

    // ───────────────────────────────────────────
    // COMMANDES — tous les utilisateurs connectés
    // ───────────────────────────────────────────
    Route::get('/orders',          [OrderController::class, 'index']);
    Route::post('/orders',         [OrderController::class, 'store']);
    Route::get('/orders/{order}',  [OrderController::class, 'show']);

    // ═══════════════════════════════════════════════
    // ROUTES ADMIN SEULEMENT
    // role:admin = vérifie que le user est admin
    // ═══════════════════════════════════════════════
    Route::middleware('role:admin')->group(function () {

        // ── Gestion des RÔLES ──
        Route::get('/roles',            [RoleController::class, 'index']);
        Route::post('/roles',           [RoleController::class, 'store']);
        Route::get('/roles/{role}',     [RoleController::class, 'show']);
        Route::put('/roles/{role}',     [RoleController::class, 'update']);
        Route::delete('/roles/{role}',  [RoleController::class, 'destroy']);

        // ── Gestion des UTILISATEURS ──
        Route::get('/users',                    [UserController::class, 'index']);
        Route::get('/users/{user}',             [UserController::class, 'show']);
        Route::put('/users/{user}/role',        [UserController::class, 'updateRole']);
        Route::delete('/users/{user}',          [UserController::class, 'destroy']);
    });

    // ═══════════════════════════════════════════════
    // ROUTES ADMIN + GESTIONNAIRE
    // ═══════════════════════════════════════════════
    Route::middleware('role:admin,gestionnaire')->group(function () {

        // Catégories
        Route::post('/categories',              [CategoryController::class, 'store']);
        Route::put('/categories/{category}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Produits
        Route::post('/products',             [ProductController::class, 'store']);
        Route::put('/products/{product}',    [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Services
        Route::post('/services',             [ServiceController::class, 'store']);
        Route::put('/services/{service}',    [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    });

    // ═══════════════════════════════════════════════
    // ROUTES ADMIN + GESTIONNAIRE + SECRÉTAIRE
    // ═══════════════════════════════════════════════
    Route::middleware('role:admin,gestionnaire,secretaire')->group(function () {
        Route::get('/orders/all',            [OrderController::class, 'allOrders']);
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });

    // ═══════════════════════════════════════════════
    // ROUTES ADMIN + COMPTABLE
    // ═══════════════════════════════════════════════
    Route::middleware('role:admin,comptable')->group(function () {
        Route::get('/reports/sales',   [OrderController::class, 'salesReport']);
        Route::get('/reports/revenue', [OrderController::class, 'revenueReport']);
    });
});
