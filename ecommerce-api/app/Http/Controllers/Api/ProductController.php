<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Liste tous les produits actifs avec filtres
     * GET /api/products
     * GET /api/products?category_id=2
     * GET /api/products?search=nike
     * GET /api/products?min_price=1000&max_price=50000
     */
    public function index(Request $request)
    {
        // On commence une query "lazy" (pas encore exécutée)
        $query = Product::with('category')  // Charge la catégorie avec chaque produit
            ->where('is_active', true); // Seulement les produits actifs

        // Filtre par catégorie si fourni
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Recherche par nom
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtre par prix min
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Filtre par prix max
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Pagination : 12 produits par page
        $products = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $products
        ]);
    }

    /**
     * Crée un nouveau produit
     * POST /api/products
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id', // L'id doit exister en BDD
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|image|max:2048', // Image max 2MB
            'is_active'   => 'boolean',
        ]);

        // Gestion de l'upload d'image
        if ($request->hasFile('image')) {
            // Stocke dans storage/app/public/products/
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $product = Product::create($validated);

        // Charge la catégorie pour la réponse
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Produit créé avec succès',
            'data'    => $product
        ], 201);
    }

    /**
     * Affiche un produit
     * GET /api/products/{id}
     */
    public function show(Product $product)
    {
        $product->load('category');

        return response()->json([
            'success' => true,
            'data'    => $product
        ]);
    }

    /**
     * Modifie un produit
     * PUT /api/products/{id}
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produit modifié avec succès',
            'data'    => $product
        ]);
    }

    /**
     * Supprime un produit
     * DELETE /api/products/{id}
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé avec succès'
        ]);
    }
}
