<?php

namespace Database\Seeders;

use App\Models\Cobranca;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CobrancaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cobrancas = [
            [
                'descricao' => 'Cobrança Cliente A',
                'valor' => 150.00,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(7),
                'telefone' => '(11) 99999-9999',
            ],
            [
                'descricao' => 'Cobrança Cliente B',
                'valor' => 200.50,
                'status' => 'pago',
                'data_vencimento' => now()->addDays(14),
                'telefone' => '(21) 88888-8888',
            ],
            [
                'descricao' => 'Cobrança Cliente C',
                'valor' => 75.25,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(21),
                'telefone' => '(31) 77777-7777',
            ],
            [
                'descricao' => 'Cobrança Cliente D',
                'valor' => 300.00,
                'status' => 'cancelado',
                'data_vencimento' => now()->addDays(30),
                'telefone' => '(41) 66666-6666',
            ],
            [
                'descricao' => 'Cobrança Cliente E',
                'valor' => 125.75,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(10),
                'telefone' => '(51) 55555-5555',
            ],
            [
                'descricao' => 'Cobrança Cliente F',
                'valor' => 450.00,
                'status' => 'pago',
                'data_vencimento' => now()->addDays(5),
                'telefone' => '(61) 44444-4444',
            ],
            [
                'descricao' => 'Cobrança Cliente G',
                'valor' => 89.99,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(15),
                'telefone' => '(71) 33333-3333',
            ],
            [
                'descricao' => 'Cobrança Cliente H',
                'valor' => 250.00,
                'status' => 'pago',
                'data_vencimento' => now()->addDays(20),
                'telefone' => '(81) 22222-2222',
            ],
            [
                'descricao' => 'Cobrança Cliente I',
                'valor' => 175.50,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(25),
                'telefone' => '(91) 11111-1111',
            ],
            [
                'descricao' => 'Cobrança Cliente J',
                'valor' => 99.00,
                'status' => 'cancelado',
                'data_vencimento' => now()->addDays(12),
                'telefone' => '(85) 00000-0000',
            ],
        ];

        foreach ($cobrancas as $cobranca) {
            Cobranca::create($cobranca);
        }
    }
}