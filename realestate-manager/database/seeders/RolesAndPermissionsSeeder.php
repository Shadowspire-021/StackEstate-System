<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        \Spatie\Permission\Models\Role::create(['name' => 'super_admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'staff']);

        $user = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'super_admin'
        ]);
        $user->assignRole('super_admin');
    }
}
