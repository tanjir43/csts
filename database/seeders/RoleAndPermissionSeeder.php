<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        # Create roles
        $adminRole      = Role::create(['name' => 'admin']);
        $customerRole   = Role::create(['name' => 'customer']);

        # Create permissions
        $permissions = [
            'view.tickets',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        # Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $customerRole->givePermissionTo([
            'view.tickets',
        ]);
    }
}
