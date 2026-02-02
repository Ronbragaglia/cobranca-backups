<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EvolutionApiService;
use App\Models\Cobranca;
use Illuminate\Support\Facades\Log;

class SendCobrancasAtrasadas extends Command
{
    protected $signature = 'cobrancas:atrasadas';
    protected $description = 'Enviar lembretes de cobranças atrasadas (D+3)';

    protected EvolutionApiService $evolution;

    public function __construct(EvolutionApiService $evolution)
    {
        parent::__construct();
        $this->evolution = $evolution;
    }

    public function handle()
    {
        $this->info('🔔 Iniciando envio de lembretes de cobranças atrasadas...');

        // Verificar se a API está conectada
        if (!$this->evolution->isConnected()) {
            $this->error('❌ Evolution API não está conectada');
            Log::error('Evolution API não está conectada ao tentar enviar lembretes de cobranças atrasadas');
            return 1;
        }

        // Buscar cobranças atrasadas (vencimento > 3 dias)
        $cobrancas = Cobranca::where('data_vencimento', '<', now()->subDays(3))
            ->where('status', '!=', 'paga')
            ->where('status', '!=', 'cancelada')
            ->where('telefone', '!=', '')
            ->where('whatsapp_reminders_sent', '<', 3)
            ->get();

        $this->info("📊 Encontradas {$cobrancas->count()} cobranças atrasadas");

        $enviadas = 0;
        $erros = 0;

        foreach ($cobrancas as $cobranca) {
            try {
                $diasAtraso = now()->diffInDays($cobranca->data_vencimento);
                $mensagem = "⚠️ *COBRANÇA ATRASADA*\n\n";
                $mensagem .= "Olá, *{$cobranca->nome}*!\n\n";
                $mensagem .= "Você tem uma cobrança em atraso:\n\n";
                $mensagem .= "Valor: *R$ " . number_format($cobranca->valor, 2, ',', '.') . "*\n";
                $mensagem .= "Vencimento: *" . $cobranca->data_vencimento->format('d/m/Y') . "*\n";
                $mensagem .= "Dias de atraso: *{$diasAtraso} dias*\n\n";
                $mensagem .= "Por favor, efetue o pagamento o mais breve possível para evitar juros.";

                $result = $this->evolution->sendTextMessage(
                    $cobranca->telefone,
                    $mensagem
                );

                if ($result['success']) {
                    $cobranca->increment('whatsapp_reminders_sent');
                    $enviadas++;
                    $this->info("✅ Lembrete enviado para {$cobranca->nome} ({$diasAtraso} dias atraso)");
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
        $this->info('✅ Envio de lembretes de cobranças atrasadas concluído');

        Log::info('Lembretes de cobranças atrasadas enviados', [
            'total' => $cobrancas->count(),
            'enviadas' => $enviadas,
            'erros' => $erros,
        ]);

        return 0;
    }
}
