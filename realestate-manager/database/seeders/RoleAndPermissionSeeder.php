<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
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

        // Create roles and assign created permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'view dashboard',
            'manage clients',
            'manage payments',
            'manage installments',
        ]);
        // Staff deliberately does NOT get:
        // delete clients, delete payments, delete installments, manage settings, manage users
    }
}
