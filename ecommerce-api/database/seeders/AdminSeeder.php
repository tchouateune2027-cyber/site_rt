<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère le rôle admin
        $adminRole = Role::where('name', Role::ADMIN)->first();

        // Crée le compte admin s'il n'existe pas
        User::firstOrCreate(
            ['email' => 'admin@ecommerce.com'],
            [
                'name'     => 'Administrateur',
                'password' => 'Admin@1234',
                // 'hashed' dans $casts → hashé automatiquement
                'role_id'  => $adminRole->id,
            ]
        );

        $this->command->info('✅ Compte admin créé !');
        $this->command->info('   Email    : admin@ecommerce.com');
        $this->command->info('   Password : Admin@1234');
    }
}
