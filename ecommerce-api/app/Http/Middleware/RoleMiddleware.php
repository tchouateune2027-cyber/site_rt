<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    // $request    = la requête HTTP entrante (contient le token, les données...)
    // $next       = la prochaine étape (le controller)
    // string ...$roles = les rôles autorisés (peut en recevoir plusieurs)
    //
    // "string ...$roles" = variadic parameter
    // Permet de passer UN ou PLUSIEURS rôles :
    //   middleware('role:admin')                → $roles = ['admin']
    //   middleware('role:admin,gestionnaire')   → $roles = ['admin', 'gestionnaire']
    {
        // VÉRIFICATION 1 : L'utilisateur est-il connecté ?
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié. Veuillez vous connecter.'
            ], 401);
            // 401 = Unauthorized (non authentifié)
            // On s'arrête ici, le controller n'est jamais appelé
        }

        // VÉRIFICATION 2 : L'utilisateur a-t-il le bon rôle ?
        $user = $request->user()->load('role');
        // load('role') = charge la relation role si pas encore chargée
        // Evite une requête SQL supplémentaire si déjà chargée

        if (!$user->hasRole($roles)) {
            // hasRole() est défini dans User.php
            // Retourne false si le rôle du user n'est pas dans $roles
            return response()->json([
                'success'      => false,
                'message'      => 'Accès refusé. Droits insuffisants.',
                'votre_role'   => $user->role?->name ?? 'aucun',
                // ?? = opérateur "null coalescing"
                // Si role?->name est NULL → affiche 'aucun'
                'roles_requis' => $roles,
            ], 403);
            // 403 = Forbidden (authentifié mais pas autorisé)
        }

        // Tout est OK → on laisse passer vers le controller
        return $next($request);
        // $next($request) = passe la requête à la prochaine étape
    }
}
