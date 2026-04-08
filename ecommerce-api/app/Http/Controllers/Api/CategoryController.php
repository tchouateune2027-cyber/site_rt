<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Liste toutes les catégories
     * GET /api/categories
     */
    public function index()
    {
        // Récupère toutes les catégories avec le nombre de produits/services
        $categories = Category::withCount(['products', 'services'])->get();

        return response()->json([
            'success' => true,
            'data'    => $categories
        ]);
    }

    /**
     * Crée une nouvelle catégorie
     * POST /api/categories
     */
    public function store(Request $request)
    {
        // Valide les données reçues
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:product,service',
        ]);

        // Génère le slug automatiquement à partir du nom
        // Ex: "Électronique & Tech" → "electronique-tech"
        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'data'    => $category
        ], 201); // 201 = Created
    }

    /**
     * Affiche une catégorie avec ses produits/services
     * GET /api/categories/{id}
     */
    public function show(Category $category)
    {
        // Charge les produits ou services selon le type
        if ($category->type === 'product') {
            $category->load('products');
        } else {
            $category->load('services');
        }

        return response()->json([
            'success' => true,
            'data'    => $category
        ]);
    }

    /**
     * Modifie une catégorie
     * PUT /api/categories/{id}
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'sometimes|in:product,service',
        ]);

        // Régénère le slug si le nom change
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie modifiée avec succès',
            'data'    => $category
        ]);
    }

    /**
     * Supprime une catégorie
     * DELETE /api/categories/{id}
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }
}
