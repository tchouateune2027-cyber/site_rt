<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Liste les commandes de l'utilisateur connecté
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $orders = Order::with('items.itemable')
            ->where('user_id', $request->user()->id)
            ->latest() // Trie par date décroissante
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders
        ]);
    }

    /**
     * Crée une commande depuis le panier
     * POST /api/orders
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string',
            'notes'            => 'nullable|string',
        ]);

        // Récupère le panier de l'utilisateur
        $cartItems = CartItem::with('itemable')
            ->where('user_id', $request->user()->id)
            ->get();

        // Vérifie que le panier n'est pas vide
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide'
            ], 422);
        }

        // DB::transaction → Si une étape échoue, TOUT est annulé (atomicité)
        $order = DB::transaction(function () use ($request, $validated, $cartItems) {

            // Calcule le total
            $total = $cartItems->sum(
                fn($item) =>
                $item->itemable->price * $item->quantity
            );

            // Crée la commande
            $order = Order::create([
                'user_id'          => $request->user()->id,
                'reference'        => 'CMD-' . strtoupper(Str::random(8)),
                'status'           => 'pending',
                'total_amount'     => $total,
                'shipping_address' => $validated['shipping_address'],
                'notes'            => $validated['notes'] ?? null,
            ]);

            // Crée les lignes de commande depuis le panier
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'itemable_type' => $cartItem->itemable_type,
                    'itemable_id'   => $cartItem->itemable_id,
                    'quantity'      => $cartItem->quantity,
                    'unit_price'    => $cartItem->itemable->price,
                    'total_price'   => $cartItem->itemable->price * $cartItem->quantity,
                ]);
            }

            // Vide le panier après commande
            CartItem::where('user_id', $request->user()->id)->delete();

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Commande passée avec succès',
            'data'    => $order->load('items.itemable')
        ], 201);
    }

    /**
     * Détail d'une commande
     * GET /api/orders/{id}
     */
    public function show(Request $request, Order $order)
    {
        // Vérifie que la commande appartient à l'utilisateur
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable'
            ], 404);
        }

        $order->load('items.itemable');

        return response()->json([
            'success' => true,
            'data'    => $order
        ]);
    }

    /**
     * Met à jour le statut d'une commande (Admin)
     * PUT /api/orders/{id}/status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled'
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'data'    => $order
        ]);
    }
}
