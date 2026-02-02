<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityAlertService
{
    public static function checkAndAlert(): void
    {
        // Verificar tentativas de login falhadas
        self::checkFailedLogins();

        // Verificar IPs suspeitos
        self::checkSuspiciousIPs();

        // Verificar uso anormal de API
        self::checkAbnormalAPIUsage();

        // Verificar tentativas de acesso não autorizado
        self::checkUnauthorizedAttempts();

        // Verificar cancelamentos de assinatura
        self::checkSubscriptionCancellations();
    }

    private static function checkFailedLogins(): void
    {
        $failedLogins = AuditLog::byAction('login')
            ->failed()
            ->recent(1)
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 5')
            ->get();

        foreach ($failedLogins as $login) {
            self::sendAlert(
                'ALERTA: Múltiplas tentativas de login falhadas',
                "IP {$login->ip_address} teve {$login->count} tentativas de login falhadas nas últimas 24h.",
                'high'
            );

            // Bloquear IP temporariamente
            Cache::put("blocked_ip:{$login->ip_address}", true, 3600);
        }
    }

    private static function checkSuspiciousIPs(): void
    {
        $suspiciousIPs = AuditLog::byAction('security_suspicious_user_agent')
            ->recent(1)
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 10')
            ->get();

        foreach ($suspiciousIPs as $ip) {
            self::sendAlert(
                'ALERTA: Atividade suspeita detectada',
                "IP {$ip->ip_address} com {$ip->count} atividades suspeitas nas últimas 24h.",
                'high'
            );

            // Bloquear IP
            Cache::put("blocked_ip:{$ip->ip_address}", true, 7200);
        }
    }

    private static function checkAbnormalAPIUsage(): void
    {
        $abnormalUsage = AuditLog::byAction('api_send')
            ->successful()
            ->recent(1)
            ->groupBy('tenant_id')
            ->havingRaw('COUNT(*) > 1000')
            ->get();

        foreach ($abnormalUsage as $usage) {
            $tenant = Tenant::find($usage->tenant_id);
            
            self::sendAlert(
                'ALERTA: Uso anormal da API detectado',
                "Tenant {$tenant->name} fez {$usage->count} requisições de API nas últimas 24h.",
                'medium',
                $tenant->id
            );
        }
    }

    private static function checkUnauthorizedAttempts(): void
    {
        $unauthorizedAttempts = AuditLog::byAction('api_send')
            ->failed()
            ->recent(1)
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 50')
            ->get();

        foreach ($unauthorizedAttempts as $attempt) {
            self::sendAlert(
                'ALERTA: Múltiplas tentativas não autorizadas',
                "IP {$attempt->ip_address} teve {$attempt->count} tentativas não autorizadas nas últimas 24h.",
                'high'
            );

            // Bloquear IP
            Cache::put("blocked_ip:{$attempt->ip_address}", true, 7200);
        }
    }

    private static function checkSubscriptionCancellations(): void
    {
        $cancellations = AuditLog::byAction('subscription_canceled')
            ->recent(1)
            ->groupBy('tenant_id')
            ->havingRaw('COUNT(*) >= 1')
            ->get();

        foreach ($cancellations as $cancellation) {
            $tenant = Tenant::find($cancellation->tenant_id);
            
            self::sendAlert(
                'ALERTA: Cancelamento de assinatura',
                "Tenant {$tenant->name} cancelou a assinatura.",
                'medium',
                $tenant->id
            );
        }
    }

    private static function sendAlert(string $subject, string $message, string $severity = 'medium', ?int $tenantId = null): void
    {
        Log::warning("[SECURITY ALERT] {$subject}: {$message}");

        // Enviar email para admin
        try {
            Mail::raw($message, function ($message) use ($subject) {
                $message->to(config('mail.admin_email', 'admin@suaempresa.com'))
                    ->subject("[{$severity}] {$subject}");
            });
        } catch (\Exception $e) {
            Log::error('Failed to send security alert email: ' . $e->getMessage());
        }

        // Enviar notificação para Telegram (opcional)
        if (config('security.telegram.enabled')) {
            self::sendTelegramAlert($subject, $message, $severity);
        }

        // Enviar notificação para Slack (opcional)
        if (config('security.slack.enabled')) {
            self::sendSlackAlert($subject, $message, $severity);
        }
    }

    private static function sendTelegramAlert(string $subject, string $message, string $severity): void
    {
        $botToken = config('security.telegram.bot_token');
        $chatId = config('security.telegram.chat_id');
        $emoji = match($severity) {
            'high' => '🚨',
            'medium' => '⚠️',
            'low' => 'ℹ️',
            default => '📋',
        };

        $text = "{$emoji} *{$subject}*\n\n{$message}";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $client = new \GuzzleHttp\Client();
        $client->post($url, [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ],
        ]);
    }

    private static function sendSlackAlert(string $subject, string $message, string $severity): void
    {
        $webhookUrl = config('security.slack.webhook_url');
        $color = match($severity) {
            'high' => '#ff0000',
            'medium' => '#ffaa00',
            'low' => '#00aa00',
            default => '#0066ff',
        };

        $client = new \GuzzleHttp\Client();
        $client->post($webhookUrl, [
            'json' => [
                'attachments' => [
                    [
                        'color' => $color,
                        'title' => $subject,
                        'text' => $message,
                        'footer' => 'Security Alert System',
                        'ts' => time(),
                    ],
                ],
            ],
        ]);
    }

    public static function logSecurityEvent(string $event, array $metadata = []): void
    {
        AuditLogService::logSecurityEvent($event, $metadata);
    }
}
