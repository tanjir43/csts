<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);

        Permission::create(['name' => 'manage tickets']);
        Permission::create(['name' => 'view all tickets']);
        Permission::create(['name' => 'update ticket status']);
        Permission::create(['name' => 'update ticket priority']);

        $adminRole->givePermissionTo(['manage tickets', 'view all tickets', 'update ticket status', 'update ticket priority']);

        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('admin');

        $customer = User::create([
            'name' => 'John Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
        ]);
        $customer->assignRole('customer');

        Ticket::factory()->create([
            'user_id' => $customer->id,
            'subject' => 'Website Login Issue',
            'description' => 'I cannot log into my account. Getting error 500.',
            'category' => 'technical',
            'priority' => 'high',
            'status' => 'open',
        ]);

        Ticket::factory()->create([
            'user_id' => $customer->id,
            'subject' => 'Billing Question',
            'description' => 'I was charged twice for my subscription.',
            'category' => 'billing',
            'priority' => 'medium',
            'status' => 'in_progress',
        ]);

        Ticket::factory()->create([
            'user_id' => $admin->id,
            'subject' => 'System Maintenance',
            'description' => 'Need to schedule maintenance window.',
            'category' => 'general',
            'priority' => 'low',
            'status' => 'resolved',
        ]);
    }
}
