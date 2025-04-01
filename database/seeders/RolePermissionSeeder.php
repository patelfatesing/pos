<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Fetch roles
        $admin = Role::where('name', 'admin')->first();
        $editor = Role::where('name', 'editor')->first();
        $user = Role::where('name', 'user')->first();

        // Fetch permissions
        $permissions = Permission::all();

        // Assign all permissions to admin
        $admin->permissions()->attach($permissions);

        // Assign specific permissions to editor
        $editor->permissions()->attach(
            Permission::whereIn('name', ['view_dashboard', 'edit_articles'])->pluck('id')->toArray()
        );

        // Assign basic permissions to user
        $user->permissions()->attach(
            Permission::whereIn('name', ['view_dashboard'])->pluck('id')->toArray()
        );
    }
}
