<?php

namespace App\Jobs;

use App\Models\Cobranca;
use App\Models\CobrancaEnvio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $cobranca;

    /**
     * Create a new job instance.
     */
    public function __construct(Cobranca $cobranca)
    {
        $this->cobranca = $cobranca;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->cobranca->telefone) {
            \Log::warning("Tentativa de envio WhatsApp sem telefone para cobrança ID {$this->cobranca->id}");
            return;
        }

        $message = "Olá! Sua cobrança '{$this->cobranca->descricao}' no valor de R$ " . number_format($this->cobranca->valor, 2, ',', '.') . " está vencida há mais de 3 dias. Entre em contato para regularizar.";

        try {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $authToken = env('TWILIO_AUTH_TOKEN');
            $from = env('TWILIO_WHATSAPP_FROM');

            if ($accountSid && $authToken && $from) {
                $response = Http::timeout(10)->withBasicAuth($accountSid, $authToken)
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                        'From' => $from,
                        'To' => 'whatsapp:' . $this->cobranca->telefone,
                        'Body' => $message,
                    ]);

                if ($response->successful()) {
                    $this->cobranca->update([
                        'notificacao_whatsapp_status' => 'enviado',
                        'ultimo_envio_whatsapp' => now(),
                    ]);
                    CobrancaEnvio::create([
                        'cobranca_id' => $this->cobranca->id,
                        'tipo' => 'whatsapp',
                        'status' => 'enviado',
                        'data' => now(),
                    ]);
                    \Log::info("WhatsApp enviado com sucesso para {$this->cobranca->telefone}, cobrança ID {$this->cobranca->id}");
                } else {
                    throw new \Exception("Erro na API Twilio: " . $response->body());
                }
            } else {
                // Simulação
                $this->cobranca->update([
                    'notificacao_whatsapp_status' => 'simulado',
                    'ultimo_envio_whatsapp' => now(),
                ]);
                CobrancaEnvio::create([
                    'cobranca_id' => $this->cobranca->id,
                    'tipo' => 'whatsapp',
                    'status' => 'simulado',
                    'data' => now(),
                ]);
                \Log::info("WhatsApp simulado para {$this->cobranca->telefone}: {$message}, cobrança ID {$this->cobranca->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Falha ao enviar WhatsApp para cobrança ID {$this->cobranca->id}: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry or failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->cobranca->update([
            'notificacao_whatsapp_status' => 'falhou',
            'ultimo_envio_whatsapp' => now(),
        ]);
        CobrancaEnvio::create([
            'cobranca_id' => $this->cobranca->id,
            'tipo' => 'whatsapp',
            'status' => 'falhou',
            'data' => now(),
            'erro' => $exception->getMessage(),
        ]);
        \Log::error("Job WhatsApp falhou definitivamente para cobrança ID {$this->cobranca->id}: " . $exception->getMessage());
        // Aqui poderia enviar notificação para o painel, mas por simplicidade, apenas log e status
    }
}