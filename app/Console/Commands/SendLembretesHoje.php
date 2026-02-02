<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EvolutionApiService;
use App\Models\Cobranca;
use Illuminate\Support\Facades\Log;

class SendLembretesHoje extends Command
{
    protected $signature = 'cobrancas:lembretes-hoje';
    protected $description = 'Enviar lembretes de cobranças vencendo hoje';

    protected EvolutionApiService $evolution;

    public function __construct(EvolutionApiService $evolution)
    {
        parent::__construct();
        $this->evolution = $evolution;
    }

    public function handle()
    {
        $this->info('🔔 Iniciando envio de lembretes de cobranças vencendo hoje...');

        // Verificar se a API está conectada
        if (!$this->evolution->isConnected()) {
            $this->error('❌ Evolution API não está conectada');
            Log::error('Evolution API não está conectada ao tentar enviar lembretes de hoje');
            return 1;
        }

        // Buscar cobranças vencendo hoje
        $cobrancas = Cobranca::whereDate('data_vencimento', today())
            ->where('status', '!=', 'paga')
            ->where('status', '!=', 'cancelada')
            ->where('telefone', '!=', '')
            ->get();

        $this->info("📊 Encontradas {$cobrancas->count()} cobranças vencendo hoje");

        $enviadas = 0;
        $erros = 0;

        foreach ($cobrancas as $cobranca) {
            try {
                $result = $this->evolution->sendReminderMessage(
                    $cobranca->telefone,
                    $cobranca->nome,
                    number_format($cobranca->valor, 2, ',', '.'),
                    $cobranca->data_vencimento->format('d/m/Y')
                );

                if ($result['success']) {
                    $cobranca->increment('whatsapp_reminders_sent');
                    $enviadas++;
                    $this->info("✅ Lembrete enviado para {$cobranca->nome}");
                } else {
                    $erros++;
                    $this->error("❌ Erro ao enviar lembrete para {$cobranca->nome}: {$result['error']}");
                }
            } catch (\Exception $e) {
                $erros++;
                $this->error("❌ Exceção ao enviar lembrete para {$cobranca->nome}: {$e->getMessage()}");
            }
        }

        $this->info("📊 Resumo: {$enviadas} enviadas, {$erros} erros");
        $this->info('✅ Envio de lembretes de hoje concluído');

        Log::info('Lembretes de hoje enviados', [
            'total' => $cobrancas->count(),
            'enviadas' => $enviadas,
            'erros' => $erros,
        ]);

        return 0;
    }
}
