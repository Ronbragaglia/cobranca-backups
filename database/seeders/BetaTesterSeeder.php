<?php

namespace Database\Seeders;

use App\Models\BetaTester;
use Illuminate\Database\Seeder;

class BetaTesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de 20 beta testers (contadores de confiança)
        $betaTesters = [
            [
                'name' => 'Roberto Silva',
                'email' => 'roberto.silva@contabilidade.com.br',
                'phone' => '5511999999991',
                'company' => 'Contabilidade Silva',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contador com mais de 50 clientes. Influenciador local.',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@contabilidade.com.br',
                'phone' => '5511999999992',
                'company' => 'Contabilidade Santos',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Especialista em contabilidade digital.',
            ],
            [
                'name' => 'João Oliveira',
                'email' => 'joao.oliveira@contabilidade.com.br',
                'phone' => '5521999999991',
                'company' => 'Contabilidade Oliveira',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Ativo em grupos de contabilidade RJ.',
            ],
            [
                'name' => 'Ana Costa',
                'email' => 'ana.costa@contabilidade.com.br',
                'phone' => '5521999999992',
                'company' => 'Contabilidade Costa',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Advocacia Costa - 150 clientes ativos.',
            ],
            [
                'name' => 'Carlos Ferreira',
                'email' => 'carlos.ferreira@contabilidade.com.br',
                'phone' => '5531999999991',
                'company' => 'Contabilidade Ferreira',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contador com foco em PMEs.',
            ],
            [
                'name' => 'Fernanda Lima',
                'email' => 'fernanda.lima@contabilidade.com.br',
                'phone' => '5531999999992',
                'company' => 'Contabilidade Lima',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Especialista em contabilidade tributária.',
            ],
            [
                'name' => 'Pedro Almeida',
                'email' => 'pedro.almeida@contabilidade.com.br',
                'phone' => '5541999999991',
                'company' => 'Contabilidade Almeida',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Ativo em grupos de contabilidade PR.',
            ],
            [
                'name' => 'Juliana Rodrigues',
                'email' => 'juliana.rodrigues@contabilidade.com.br',
                'phone' => '5541999999992',
                'company' => 'Contabilidade Rodrigues',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contadora com mais de 100 clientes.',
            ],
            [
                'name' => 'Ricardo Pereira',
                'email' => 'ricardo.pereira@contabilidade.com.br',
                'phone' => '5551999999991',
                'company' => 'Contabilidade Pereira',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Especialista em contabilidade empresarial.',
            ],
            [
                'name' => 'Camila Martins',
                'email' => 'camila.martins@contabilidade.com.br',
                'phone' => '5551999999992',
                'company' => 'Contabilidade Martins',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Ativa em grupos de contabilidade RS.',
            ],
            [
                'name' => 'Lucas Gomes',
                'email' => 'lucas.gomes@contabilidade.com.br',
                'phone' => '5561999999991',
                'company' => 'Contabilidade Gomes',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contador com foco em startups.',
            ],
            [
                'name' => 'Patricia Rocha',
                'email' => 'patricia.rocha@contabilidade.com.br',
                'phone' => '5561999999992',
                'company' => 'Contabilidade Rocha',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Especialista em contabilidade digital.',
            ],
            [
                'name' => 'Marcos Barbosa',
                'email' => 'marcos.barbosa@contabilidade.com.br',
                'phone' => '5571999999991',
                'company' => 'Contabilidade Barbosa',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Ativo em grupos de contabilidade BA.',
            ],
            [
                'name' => 'Amanda Souza',
                'email' => 'amanda.souza@contabilidade.com.br',
                'phone' => '5571999999992',
                'company' => 'Contabilidade Souza',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contadora com mais de 80 clientes.',
            ],
            [
                'name' => 'Felipe Cardoso',
                'email' => 'felipe.cardoso@contabilidade.com.br',
                'phone' => '5581999999991',
                'company' => 'Contabilidade Cardoso',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Especialista em contabilidade tributária.',
            ],
            [
                'name' => 'Beatriz Alves',
                'email' => 'beatriz.alves@contabilidade.com.br',
                'phone' => '5581999999992',
                'company' => 'Contabilidade Alves',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Ativa em grupos de contabilidade PE.',
            ],
            [
                'name' => 'Gustavo Mendes',
                'email' => 'gustavo.mendes@contabilidade.com.br',
                'phone' => '5591999999991',
                'company' => 'Contabilidade Mendes',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contador com foco em PMEs.',
            ],
            [
                'name' => 'Larissa Nunes',
                'email' => 'larissa.nunes@contabilidade.com.br',
                'phone' => '5591999999992',
                'company' => 'Contabilidade Nunes',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Especialista em contabilidade digital.',
            ],
            [
                'name' => 'Rafael Carvalho',
                'email' => 'rafael.carvalho@contabilidade.com.br',
                'phone' => '5511999999993',
                'company' => 'Contabilidade Carvalho',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Ativo em grupos de contabilidade SP.',
            ],
            [
                'name' => 'Isabela Torres',
                'email' => 'isabela.torres@contabilidade.com.br',
                'phone' => '5511999999994',
                'company' => 'Contabilidade Torres',
                'segment' => 'contabilidade',
                'discount_percentage' => 50,
                'notes' => 'Contadora com mais de 120 clientes.',
            ],
        ];

        foreach ($betaTesters as $betaTester) {
            BetaTester::create($betaTester);
        }

        $this->command->info('20 beta testers criados com sucesso!');
    }
}
