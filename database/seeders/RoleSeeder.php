<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid duplicates if you run seeder multiple times
        $now = now();

        DB::table('roles')->upsert([
            ['name' => 'Super Admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Admin',       'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cashier',     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Customer',    'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['updated_at']);
    }
}
