<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MultiTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicPlan = Plan::where('slug', 'basic')->first();
        $proPlan = Plan::where('slug', 'pro')->first();
        $enterprisePlan = Plan::where('slug', 'enterprise')->first();

        // Criar 100 tenants
        for ($i = 1; $i <= 100; $i++) {
            $subdomain = "evolution-cliente{$i}";
            
            // Determinar plano baseado no índice
            $plan = match(true) {
                $i <= 50 => $basicPlan,    // 50 clientes no plano básico
                $i <= 80 => $proPlan,       // 30 clientes no plano pro
                default => $enterprisePlan, // 20 clientes no enterprise
            };

            // Criar tenant
            $tenant = Tenant::create([
                'name' => "Cliente {$i} - Evolution",
                'subdomain' => $subdomain,
                'stripe_customer_id' => null,
                'subscription_status' => 'active',
                'evolution_api_key' => "evolution_api_key_{$subdomain}",
                'evolution_api_url' => "https://api.evolution.com/instance/{$subdomain}",
                'evolution_instances' => [
                    [
                        'name' => "{$subdomain}_main",
                        'status' => 'connected',
                        'phone' => "551199999" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    ]
                ],
                'qr_code_image' => null,
                'custom_qr_enabled' => $plan->custom_qr,
                'max_whatsapp_instances' => $plan->max_whatsapp_instances,
                'max_messages_per_month' => $plan->max_messages_per_month,
                'current_messages_month' => 0,
                'usage_reset_at' => now()->startOfMonth(),
                'active' => true,
                'trial_ends_at' => null,
            ]);

            // Criar usuário admin do tenant
            $user = User::create([
                'name' => "Admin Cliente {$i}",
                'email' => "admin{$i}@cliente{$i}.com",
                'password' => Hash::make('password123'),
                'tenant_id' => $tenant->id,
            ]);

            // Criar assinatura
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => "sub_{$subdomain}_" . Str::random(20),
                'stripe_customer_id' => "cus_{$subdomain}_" . Str::random(20),
                'stripe_status' => 'active',
                'status' => 'active',
                'trial_ends_at' => null,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'canceled_at' => null,
                'current_messages_month' => 0,
                'current_whatsapp_instances' => 1,
                'usage_reset_at' => now()->startOfMonth(),
            ]);

            // Criar API key para o tenant
            $apiKey = \App\Models\ApiKey::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'name' => "API Key Principal",
                'key' => \App\Models\ApiKey::generateKey(),
                'prefix' => null,
                'abilities' => ['*'],
                'rate_limit_per_minute' => 60,
                'rate_limit_per_hour' => 1000,
                'total_requests' => 0,
                'last_used_at' => null,
                'active' => true,
                'expires_at' => null,
            ]);

            $this->command->info("Tenant {$i} ({$subdomain}) criado com plano {$plan->name}");
        }

        $this->command->info('100 tenants criados com sucesso!');
    }
}
