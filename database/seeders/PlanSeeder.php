<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Básico',
                'slug' => 'basic',
                'description' => 'Ideal para pequenos negócios que estão começando',
                'price' => 97.00,
                'currency' => 'BRL',
                'interval' => 1,
                'max_whatsapp_instances' => 1,
                'max_messages_per_month' => 500,
                'unlimited_messages' => false,
                'api_access' => true,
                'custom_qr' => false,
                'analytics' => false,
                'priority_support' => false,
                'features' => [
                    '1 instância do WhatsApp',
                    '500 mensagens por mês',
                    'Acesso à API',
                    'Upload de CSV',
                    'Templates básicos',
                    'Suporte por email',
                ],
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Para empresas em crescimento que precisam de mais recursos',
                'price' => 297.00,
                'currency' => 'BRL',
                'interval' => 1,
                'max_whatsapp_instances' => 3,
                'max_messages_per_month' => 5000,
                'unlimited_messages' => false,
                'api_access' => true,
                'custom_qr' => true,
                'analytics' => true,
                'priority_support' => false,
                'features' => [
                    '3 instâncias do WhatsApp',
                    '5.000 mensagens por mês',
                    'Acesso à API completa',
                    'Upload de CSV',
                    'Templates avançados',
                    'QR personalizado',
                    'Analytics e relatórios',
                    'Suporte prioritário',
                ],
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Para grandes empresas com alto volume de mensagens',
                'price' => 997.00,
                'currency' => 'BRL',
                'interval' => 1,
                'max_whatsapp_instances' => 10,
                'max_messages_per_month' => 0,
                'unlimited_messages' => true,
                'api_access' => true,
                'custom_qr' => true,
                'analytics' => true,
                'priority_support' => true,
                'features' => [
                    '10 instâncias do WhatsApp',
                    'Mensagens ilimitadas',
                    'Acesso à API completa',
                    'Upload de CSV',
                    'Templates avançados',
                    'QR personalizado',
                    'Analytics avançados',
                    'Suporte dedicado 24/7',
                    'SLA garantido',
                    'Integração customizada',
                ],
                'active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
