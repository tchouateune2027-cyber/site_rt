<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // ─────────────────────────────────────────────────
    // LISTER tous les rôles
    // Méthode : GET
    // URL     : /api/roles
    // Accès   : Admin seulement
    //
    // Quand l'admin veut voir tous les rôles existants
    // ─────────────────────────────────────────────────
    public function index()
    {
        // withCount('users') = ajoute une colonne "users_count"
        // qui indique combien de users ont chaque rôle
        // Exemple : { "name": "admin", "users_count": 3 }
        $roles = Role::withCount('users')->get();
        // withCount fait ce SQL :
        // SELECT roles.*, COUNT(users.id) as users_count
        // FROM roles
        // LEFT JOIN users ON users.role_id = roles.id
        // GROUP BY roles.id

        return response()->json([
            'success' => true,
            'data'    => $roles
        ]);
        // response()->json() = crée une réponse HTTP avec du JSON
        // Laravel met automatiquement le header Content-Type: application/json
    }

    // ─────────────────────────────────────────────────
    // CRÉER un nouveau rôle
    // Méthode : POST
    // URL     : /api/roles
    // Accès   : Admin seulement
    //
    // Quand l'admin veut ajouter un nouveau type de rôle
    // Exemple : ajouter un rôle "livreur"
    // ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        // ÉTAPE 1 : Valider les données reçues
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            // required     = ce champ est obligatoire
            // string       = doit être du texte
            // max:255      = maximum 255 caractères
            // unique:roles,name = la valeur doit être unique
            //                    dans la colonne "name" de la table "roles"
            //                    Empêche de créer 2 rôles "admin"

            'description' => 'nullable|string|max:255',
            // nullable = ce champ est optionnel (peut être absent)
        ]);
        // Si la validation échoue → Laravel retourne automatiquement
        // une erreur 422 avec le détail des champs invalides
        // Tu n'as pas besoin de gérer ça toi-même

        // ÉTAPE 2 : Créer le rôle en BDD
        $role = Role::create($validated);
        // create() = INSERT INTO roles (name, description) VALUES (...)
        // $validated contient uniquement les champs validés
        // C'est plus sûr que de passer $request->all()

        // ÉTAPE 3 : Retourner la réponse
        return response()->json([
            'success' => true,
            'message' => 'Rôle créé avec succès',
            'data'    => $role
        ], 201);
        // 201 = Created (ressource créée avec succès)
    }

    // ─────────────────────────────────────────────────
    // VOIR un rôle spécifique avec ses utilisateurs
    // Méthode : GET
    // URL     : /api/roles/{id}
    // Accès   : Admin seulement
    // ─────────────────────────────────────────────────
    public function show(Role $role)
    // "Role $role" = Route Model Binding
    // Laravel cherche automatiquement le rôle avec l'id de l'URL
    // Si /api/roles/3 → Laravel fait Role::findOrFail(3)
    // Si id=3 n'existe pas → Laravel retourne 404 automatiquement
    {
        // Charge les users qui ont ce rôle
        $role->load('users');
        // load() = charge la relation "users" définie dans Role.php
        // SQL : SELECT * FROM users WHERE role_id = {id du rôle}

        return response()->json([
            'success' => true,
            'data'    => $role
        ]);
    }

    // ─────────────────────────────────────────────────
    // MODIFIER un rôle existant
    // Méthode : PUT
    // URL     : /api/roles/{id}
    // Accès   : Admin seulement
    // ─────────────────────────────────────────────────
    public function update(Request $request, Role $role)
    {
        // Vérifie qu'on ne modifie pas les rôles de base
        // Ces rôles sont essentiels au fonctionnement du système
        $rolesProtégés = [
            Role::USER,
            Role::ADMIN,
            Role::GESTIONNAIRE,
            Role::SECRETAIRE,
            Role::COMPTABLE,
        ];

        if (in_array($role->name, $rolesProtégés)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle est protégé et ne peut pas être modifié.'
            ], 403);
            // 403 = Forbidden (interdit)
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            // sometimes = valide SEULEMENT si le champ est présent
            //             Permet de modifier uniquement description
            //             sans re-envoyer le name
            //
            // unique:roles,name,{id} = vérifie l'unicité SAUF pour ce rôle
            // Ex: si le rôle s'appelle déjà "livreur" et qu'on envoie
            // name="livreur" → pas d'erreur (c'est le même rôle)

            'description' => 'nullable|string|max:255',
        ]);

        $role->update($validated);
        // UPDATE roles SET name=..., description=... WHERE id={id}

        return response()->json([
            'success' => true,
            'message' => 'Rôle modifié avec succès',
            'data'    => $role
        ]);
    }

    // ─────────────────────────────────────────────────
    // SUPPRIMER un rôle
    // Méthode : DELETE
    // URL     : /api/roles/{id}
    // Accès   : Admin seulement
    // ─────────────────────────────────────────────────
    public function destroy(Role $role)
    {
        // SÉCURITÉ 1 : Empêcher la suppression des rôles de base
        // Si on supprime "admin" → plus d'administrateur possible !
        $rolesProtégés = [
            Role::USER,
            Role::ADMIN,
            Role::GESTIONNAIRE,
            Role::SECRETAIRE,
            Role::COMPTABLE,
        ];

        if (in_array($role->name, $rolesProtégés)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle est protégé et ne peut pas être supprimé.'
            ], 403);
        }

        // SÉCURITÉ 2 : Vérifier si des users ont ce rôle
        $nombreUsers = $role->users()->count();
        // users() = la relation définie dans Role.php
        // count() = SQL : SELECT COUNT(*) FROM users WHERE role_id = {id}

        if ($nombreUsers > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce rôle. ' .
                    $nombreUsers . ' utilisateur(s) l\'ont encore.',
                'nombre_users' => $nombreUsers
            ], 422);
            // 422 = Unprocessable Entity
            // La requête est valide mais impossible à traiter dans ce contexte
        }

        // Tout est OK → on supprime
        $role->delete();
        // DELETE FROM roles WHERE id = {id}

        return response()->json([
            'success' => true,
            'message' => 'Rôle supprimé avec succès'
        ]);
    }
}
