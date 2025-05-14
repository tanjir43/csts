<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        # Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);

        # Create permissions (optional)
        Permission::create(['name' => 'manage tickets']);
        Permission::create(['name' => 'view all tickets']);
        Permission::create(['name' => 'update ticket status']);
        Permission::create(['name' => 'update ticket priority']);

        # Assign permissions to roles
        $adminRole->givePermissionTo(['manage tickets', 'view all tickets', 'update ticket status', 'update ticket priority']);

        # Create admin user
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('admin');

        # Create a customer user
        $customer = User::create([
            'name' => 'John Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
        ]);
        $customer->assignRole('customer');

        # Create some sample tickets
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
