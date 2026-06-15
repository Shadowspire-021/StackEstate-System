<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage clients',
            'delete clients',
            'manage payments',
            'delete payments',
            'manage installments',
            'delete installments',
            'manage settings',
            'manage users',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // Super admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Staff gets limited permissions (no delete, no settings, no users)
        $staff->givePermissionTo([
            'view dashboard',
            'manage clients',
            'manage payments',
            'manage installments',
        ]);

        // Ensure default admin user exists and has super_admin role
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'super_admin',
            ]
        );
        $user->assignRole('super_admin');
    }
}
