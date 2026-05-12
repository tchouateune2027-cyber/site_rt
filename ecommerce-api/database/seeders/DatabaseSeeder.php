<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // L'ORDRE EST IMPORTANT !
        // RoleSeeder en premier car AdminSeeder a besoin des rôles
        $this->call([
            RoleSeeder::class,   // 1. Crée les rôles
            AdminSeeder::class,  // 2. Crée le compte admin
        ]);
    }
}
