<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si l'utilisateur connecté est admin
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([
                'message' => 'Accès refusé. Droits administrateur requis.'
            ], 403); // 403 = Forbidden
        }

        // Si admin → laisse passer la requête
        return $next($request);
    }
}
