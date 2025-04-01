<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        Permission::insert([
            ['name' => 'view_dashboard', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_users', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit_articles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete_articles', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
