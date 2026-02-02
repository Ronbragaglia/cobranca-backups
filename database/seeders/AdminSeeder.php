<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate([
            'subdomain' => 'principal',
        ], [
            'name' => 'Principal',
            'subscription_status' => 'active',
        ]);

        User::firstOrCreate([
            'email' => 'admin@cobranca.com',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
            'tenant_id' => $tenant->id,
        ]);
    }
}