<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Affiche le panier de l'utilisateur connecté
     * GET /api/cart
     */
    public function index(Request $request)
    {
        $cartItems = CartItem::with('itemable') // Charge Product ou Service
            ->where('user_id', $request->user()->id)
            ->get();

        // Calcule le total du panier
        $total = $cartItems->sum(function ($item) {
            return $item->itemable->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'data'    => $cartItems,
            'total'   => $total
        ]);
    }

    /**
     * Ajoute un article au panier
     * POST /api/cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'itemable_type' => 'required|in:product,service',
            'itemable_id'   => 'required|integer',
            'quantity'      => 'required|integer|min:1',
        ]);

        // Convertit 'product' → 'App\Models\Product'
        $modelClass = $validated['itemable_type'] === 'product'
            ? Product::class
            : Service::class;

        // Vérifie que l'article existe
        $item = $modelClass::findOrFail($validated['itemable_id']);

        // Vérifie le stock si c'est un produit
        if ($item instanceof Product && $item->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant. Disponible : ' . $item->stock
            ], 422);
        }

        // updateOrCreate → Si l'article existe déjà dans le panier : met à jour
        //                   Sinon : crée une nouvelle ligne
        $cartItem = CartItem::updateOrCreate(
            [
                'user_id'       => $request->user()->id,
                'itemable_type' => $modelClass,
                'itemable_id'   => $validated['itemable_id'],
            ],
            ['quantity' => $validated['quantity']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Article ajouté au panier',
            'data'    => $cartItem->load('itemable')
        ], 201);
    }

    /**
     * Modifie la quantité d'un article
     * PUT /api/cart/{cartItem}
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Vérifie que le panier appartient bien à l'utilisateur connecté
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quantité mise à jour',
            'data'    => $cartItem
        ]);
    }

    /**
     * Retire un article du panier
     * DELETE /api/cart/{cartItem}
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article retiré du panier'
        ]);
    }
}
