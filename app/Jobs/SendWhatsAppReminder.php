<?php

namespace App\Jobs;

use App\Models\Cobranca;
use App\Models\CobrancaEnvio;
use App\Models\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWhatsAppReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $cobranca;
    protected $reminderType;

    /**
     * Create a new job instance.
     */
    public function __construct(Cobranca $cobranca, string $reminderType)
    {
        $this->cobranca = $cobranca;
        $this->reminderType = $reminderType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Verificar se a cobrança tem telefone
        if (!$this->cobranca->telefone) {
            \Log::warning("Tentativa de envio WhatsApp sem telefone para cobrança ID {$this->cobranca->id}");
            return;
        }

        // Verificar se o lembrete já foi enviado
        $reminders = $this->cobranca->whatsapp_reminders_sent ?? [];
        if (in_array($this->reminderType, $reminders)) {
            \Log::info("Lembrete {$this->reminderType} já enviado para cobrança ID {$this->cobranca->id}");
            return;
        }

        // Verificar configurações do tenant
        $tenant = $this->cobranca->tenant;
        if (!$tenant) {
            \Log::warning("Cobrança ID {$this->cobranca->id} não possui tenant");
            return;
        }

        $settings = $tenant->settings;
        if (!$settings || !$settings->whatsapp_enabled) {
            \Log::info("Lembretes de WhatsApp desabilitados para tenant {$tenant->id}");
            return;
        }

        // Verificar se deve enviar este tipo de lembrete
        $daysDiff = $this->calculateDaysDiff();
        $type = $this->getReminderType();
        
        if (!$settings->shouldSendReminder($type, $daysDiff)) {
            \Log::info("Lembrete {$this->reminderType} não configurado para ser enviado para cobrança ID {$this->cobranca->id}");
            return;
        }

        $message = $this->getMessage();

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
                    $this->updateCobranca();
                    CobrancaEnvio::create([
                        'cobranca_id' => $this->cobranca->id,
                        'tipo' => 'whatsapp_reminder_' . $this->reminderType,
                        'status' => 'enviado',
                        'data' => now(),
                    ]);
                    \Log::info("WhatsApp reminder {$this->reminderType} enviado com sucesso para {$this->cobranca->telefone}, cobrança ID {$this->cobranca->id}");
                } else {
                    throw new \Exception("Erro na API Twilio: " . $response->body());
                }
            } else {
                // Simulação
                $this->updateCobranca();
                CobrancaEnvio::create([
                    'cobranca_id' => $this->cobranca->id,
                    'tipo' => 'whatsapp_reminder_' . $this->reminderType,
                    'status' => 'simulado',
                    'data' => now(),
                ]);
                \Log::info("WhatsApp reminder {$this->reminderType} simulado para {$this->cobranca->telefone}: {$message}, cobrança ID {$this->cobranca->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Falha ao enviar WhatsApp reminder {$this->reminderType} para cobrança ID {$this->cobranca->id}: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry or failed
        }
    }

    /**
     * Calcular diferença em dias até o vencimento
     */
    private function calculateDaysDiff(): int
    {
        if (!$this->cobranca->data_vencimento) {
            return 0;
        }
        
        $now = now()->startOfDay();
        $dueDate = $this->cobranca->data_vencimento->startOfDay();
        
        return $dueDate->diffInDays($now, false);
    }

    /**
     * Determinar o tipo de lembrete (before, on_due_date, after)
     */
    private function getReminderType(): string
    {
        $daysDiff = $this->calculateDaysDiff();
        
        if ($daysDiff > 0) {
            return 'before';
        } elseif ($daysDiff === 0) {
            return 'on_due_date';
        } else {
            return 'after';
        }
    }

    private function getMessage(): string
    {
        $valor = number_format($this->cobranca->valor, 2, ',', '.');
        $data = $this->cobranca->data_vencimento->format('d/m');

        switch ($this->reminderType) {
            case 'd-7':
                return "Lembrete: {$this->cobranca->descricao} de R$ {$valor} vence em 7 dias ({$data})";
            case 'd-5':
                return "Lembrete: {$this->cobranca->descricao} de R$ {$valor} vence em 5 dias ({$data})";
            case 'd-3':
                return "Lembrete: {$this->cobranca->descricao} de R$ {$valor} vence em 3 dias ({$data})";
            case 'd-2':
                return "Lembrete: {$this->cobranca->descricao} de R$ {$valor} vence em 2 dias ({$data})";
            case 'd-1':
                return "URGENTE: {$this->cobranca->descricao} de R$ {$valor} vence amanhã ({$data})";
            case 'd0':
                return "VENCE HOJE: {$this->cobranca->descricao} de R$ {$valor} - pague agora!";
            case 'd+1':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "VENCIDA: {$this->cobranca->descricao} de R$ {$valor} - pague agora{$link}";
            case 'd+2':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 2 dias - pague agora{$link}";
            case 'd+3':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 3 dias - pague agora{$link}";
            case 'd+5':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 5 dias - pague agora{$link}";
            case 'd+7':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 7 dias - pague agora{$link}";
            case 'd+10':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 10 dias - pague agora{$link}";
            case 'd+15':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 15 dias - pague agora{$link}";
            case 'd+30':
                $link = $this->cobranca->stripe_payment_link ? " {$this->cobranca->stripe_payment_link}" : "";
                return "ATENÇÃO: {$this->cobranca->descricao} de R$ {$valor} está vencida há 30 dias - pague agora{$link}";
            default:
                return "Lembrete de cobrança R$ {$valor}";
        }
    }

    private function updateCobranca(): void
    {
        $reminders = $this->cobranca->whatsapp_reminders_sent ?? [];
        $reminders[] = $this->reminderType;
        $this->cobranca->update([
            'whatsapp_reminders_sent' => $reminders,
        ]);
    }
}