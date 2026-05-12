<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // ─────────────────────────────────────────────────
    // LISTER tous les utilisateurs
    // Méthode : GET
    // URL     : /api/users
    // Accès   : Admin seulement
    //
    // L'admin veut voir tous les users et leurs rôles
    // ─────────────────────────────────────────────────
    public function index()
    {
        $users = User::with('role')
            // with('role') = Eager Loading
            // Charge la relation "role" pour chaque user
            // en UNE SEULE requête SQL supplémentaire
            //
            // SANS with('role') : N+1 problème
            // SQL 1 : SELECT * FROM users           (10 users)
            // SQL 2 : SELECT * FROM roles WHERE id = 1
            // SQL 3 : SELECT * FROM roles WHERE id = 2
            // SQL 4 : SELECT * FROM roles WHERE id = 3
            // ... 10 requêtes pour 10 users !
            //
            // AVEC with('role') : seulement 2 requêtes
            // SQL 1 : SELECT * FROM users
            // SQL 2 : SELECT * FROM roles WHERE id IN (1,2,3...)

            ->latest()
            // Trie par date de création décroissante
            // Le user le plus récent apparaît en premier
            // SQL : ORDER BY created_at DESC

            ->get();
        // Exécute la requête et retourne une Collection

        return response()->json([
            'success' => true,
            'total'   => $users->count(),
            // count() = nombre total de users
            'data'    => $users
        ]);
    }

    // ─────────────────────────────────────────────────
    // VOIR un utilisateur spécifique
    // Méthode : GET
    // URL     : /api/users/{id}
    // Accès   : Admin seulement
    // ─────────────────────────────────────────────────
    public function show(User $user)
    {
        $user->load('role');
        // load() = charge la relation si pas encore chargée
        // Similaire à with() mais pour un objet déjà récupéré

        return response()->json([
            'success' => true,
            'data'    => $user
        ]);
    }

    // ─────────────────────────────────────────────────
    // ATTRIBUER un rôle à un utilisateur
    // Méthode : PUT
    // URL     : /api/users/{id}/role
    // Accès   : Admin seulement
    //
    // C'est l'action principale :
    // L'admin choisit un user et lui assigne un rôle
    // ─────────────────────────────────────────────────
    public function updateRole(Request $request, User $user)
    {
        // ÉTAPE 1 : Valider les données
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            // required       = obligatoire
            // exists:roles,id = le role_id DOIT exister dans la table roles
            //                   Si on envoie role_id=99 et que ça n'existe pas
            //                   → erreur de validation automatique
        ]);

        // ÉTAPE 2 : Sécurité — L'admin ne peut pas changer son propre rôle
        // Pour éviter qu'il se retire lui-même les droits admin
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier votre propre rôle.'
            ], 403);
        }

        // ÉTAPE 3 : Récupère l'ancien rôle pour le message
        $ancienRole = $user->role?->name ?? 'aucun';
        // ?->    = si role est NULL, retourne NULL
        // ?? 'aucun' = si NULL, affiche 'aucun'

        // ÉTAPE 4 : Met à jour le rôle
        $user->update(['role_id' => $validated['role_id']]);
        // UPDATE users SET role_id = {role_id} WHERE id = {id}

        // ÉTAPE 5 : Recharge le user avec son nouveau rôle
        $user->load('role');
        // Sans load(), $user->role retournerait encore l'ancien rôle
        // car Laravel garde les relations en mémoire (cache)
        // load() force le rechargement depuis la BDD

        return response()->json([
            'success'     => true,
            'message'     => 'Rôle mis à jour avec succès',
            'utilisateur' => $user->name,
            'ancien_role' => $ancienRole,
            'nouveau_role' => $user->role->name,
            'data'        => $user
        ]);
    }

    // ─────────────────────────────────────────────────
    // SUPPRIMER un utilisateur
    // Méthode : DELETE
    // URL     : /api/users/{id}
    // Accès   : Admin seulement
    // ─────────────────────────────────────────────────
    public function destroy(Request $request, User $user)
    {
        // Sécurité : L'admin ne peut pas se supprimer lui-même
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.'
            ], 403);
        }

        // Sécurité : Ne pas supprimer un autre admin
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un compte administrateur.'
            ], 403);
        }

        $user->delete();
        // DELETE FROM users WHERE id = {id}

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}
