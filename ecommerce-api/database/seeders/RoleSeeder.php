<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crée les rôles s'ils n'existent pas déjà
        // firstOrCreate = cherche d'abord, crée seulement si introuvable
        // Evite les doublons si on relance le seeder

        Role::firstOrCreate(
            ['name' => Role::USER],
            ['description' => 'Client standard de la plateforme']
        );

        Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['description' => 'Administrateur avec accès complet']
        );

        Role::firstOrCreate(
            ['name' => Role::GESTIONNAIRE],
            ['description' => 'Gère les produits, services et commandes']
        );

        Role::firstOrCreate(
            ['name' => Role::SECRETAIRE],
            ['description' => 'Gère les clients et le suivi des commandes']
        );

        Role::firstOrCreate(
            ['name' => Role::COMPTABLE],
            ['description' => 'Accès aux rapports financiers et facturation']
        );

        // Affiche un message dans le terminal
        $this->command->info('✅ Rôles créés avec succès !');
    }
}
