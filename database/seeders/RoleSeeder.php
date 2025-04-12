<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = ['admin', 'editor', 'user', 'warehouse', 'cashier','owner'];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(
                ['name' => $role],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        
    }
}