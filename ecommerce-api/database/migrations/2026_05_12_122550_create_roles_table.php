<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────
        // ÉTAPE 1 : Créer la table roles
        // ──────────────────────────────────────────
        Schema::create('roles', function (Blueprint $table) {

            $table->id();
            // id auto-incrémenté : 1, 2, 3...

            $table->string('name')->unique();
            // Nom du rôle : "user", "admin", "gestionnaire"...
            // unique() = deux rôles ne peuvent pas avoir le même nom

            $table->string('description')->nullable();
            // Description lisible du rôle
            // Ex: "Administrateur avec tous les droits"

            $table->timestamps();
        });

        // ──────────────────────────────────────────
        // ÉTAPE 2 : Ajouter role_id dans users
        // ──────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('role_id')
                ->nullable()
                // nullable() car on insère les rôles APRÈS
                // la création de la table users
                ->constrained('roles')
                // constrained('roles') = pointe vers la table roles
                ->onDelete('set null')
                // Si un rôle est supprimé →
                // role_id des users devient NULL (pas de cascade)
                ->after('email');
            // Place la colonne après email
        });
    }

    public function down(): void
    {
        // Pour annuler : d'abord supprimer la clé étrangère
        // puis les tables dans l'ordre inverse
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            // Supprime la contrainte de clé étrangère
            $table->dropColumn('role_id');
            // Puis supprime la colonne
        });

        Schema::dropIfExists('roles');
    }
};
