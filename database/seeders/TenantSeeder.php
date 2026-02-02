<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
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
            'email' => 'admin@seucrm.com',
        ], [
            'name' => 'Admin Principal',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
        ]);

        // Criar tenant demo para testes locais
        $demoTenant = Tenant::firstOrCreate([
            'subdomain' => 'demo',
        ], [
            'name' => 'Demo Tenant',
            'subscription_status' => 'active',
        ]);

        User::firstOrCreate([
            'email' => 'demo@seucrm.com',
        ], [
            'name' => 'Admin Demo',
            'password' => Hash::make('password'),
            'tenant_id' => $demoTenant->id,
        ]);
    }
}