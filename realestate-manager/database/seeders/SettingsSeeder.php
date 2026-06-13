<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            ['key' => 'company_name', 'value' => 'Real Estate Co.'],
            ['key' => 'company_address', 'value' => '123 Main St.'],
            ['key' => 'vendor_name', 'value' => 'John Doe'],
            ['key' => 'vendor_cnic', 'value' => '00000-0000000-0'],
        ]);
    }
}
