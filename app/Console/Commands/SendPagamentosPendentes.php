<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EvolutionApiService;
use App\Models\Cobranca;
use Illuminate\Support\Facades\Log;

class SendPagamentosPendentes extends Command
{
    protected $signature = 'cobrancas:pendentes';
    protected $description = 'Enviar lembretes de pagamentos pendentes (D+7)';

    protected EvolutionApiService $evolution;

    public function __construct(EvolutionApiService $evolution)
    {
        parent::__construct();
        $this->evolution = $evolution;
    }

    public function handle()
    {
        $this->info('🔔 Iniciando envio de lembretes de pagamentos pendentes...');

        // Verificar se a API está conectada
        if (!$this->evolution->isConnected()) {
            $this->error('❌ Evolution API não está conectada');
            Log::error('Evolution API não está conectada ao tentar enviar lembretes de pagamentos pendentes');
            return 1;
        }

        // Buscar cobranças pendentes (vencimento > 7 dias)
        $cobrancas = Cobranca::where('data_vencimento', '<', now()->subDays(7))
            ->where('status', '!=', 'paga')
            ->where('status', '!=', 'cancelada')
            ->where('telefone', '!=', '')
            ->where('whatsapp_reminders_sent', '<', 5)
            ->get();

        $this->info("📊 Encontradas {$cobrancas->count()} cobranças pendentes (D+7)");

        $enviadas = 0;
        $erros = 0;

        foreach ($cobrancas as $cobranca) {
            try {
                $diasAtraso = now()->diffInDays($cobranca->data_vencimento);
                $mensagem = "🔔 *LEMBRETE DE PAGAMENTO*\n\n";
                $mensagem .= "Olá, *{$cobranca->nome}*!\n\n";
                $mensagem .= "Você tem um pagamento pendente:\n\n";
                $mensagem .= "Valor: *R$ " . number_format($cobranca->valor, 2, ',', '.') . "*\n";
                $mensagem .= "Vencimento: *" . $cobranca->data_vencimento->format('d/m/Y') . "*\n";
                $mensagem .= "Dias de atraso: *{$diasAtraso} dias*\n\n";

                // Adicionar link de pagamento se disponível
                if ($cobranca->stripe_payment_link) {
                    $mensagem .= "🔗 Link para pagamento:\n{$cobranca->stripe_payment_link}\n\n";
                }

                $mensagem .= "Por favor, efetue o pagamento o mais breve possível.";

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
        $this->info('✅ Envio de lembretes de pagamentos pendentes concluído');

        Log::info('Lembretes de pagamentos pendentes enviados', [
            'total' => $cobrancas->count(),
            'enviadas' => $enviadas,
            'erros' => $erros,
        ]);

        return 0;
    }
}
