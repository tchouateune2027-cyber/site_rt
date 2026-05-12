<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Liste tous les services actifs
     * GET /api/services
     */
    public function index(Request $request)
    {
        $query = Service::with('category')
            ->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $services = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $services
        ]);
    }

    /**
     * Crée un nouveau service
     * POST /api/services
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'duration'    => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('services', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $service = Service::create($validated);
        $service->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Service créé avec succès',
            'data'    => $service
        ], 201);
    }

    /**
     * Affiche un service
     * GET /api/services/{id}
     */
    public function show(Service $service)
    {
        $service->load('category');

        return response()->json([
            'success' => true,
            'data'    => $service
        ]);
    }

    /**
     * Modifie un service
     * PUT /api/services/{id}
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'duration'    => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('services', 'public');
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service modifié avec succès',
            'data'    => $service
        ]);
    }

    /**
     * Supprime un service
     * DELETE /api/services/{id}
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service supprimé avec succès'
        ]);
    }
}
