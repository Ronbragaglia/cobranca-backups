<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettings extends Model
{
    protected $fillable = [
        'tenant_id',
        'whatsapp_reminder_days_before',
        'whatsapp_reminder_on_due_date',
        'whatsapp_reminder_days_after',
        'whatsapp_enabled',
        'default_currency',
        'default_due_days',
    ];

    protected $casts = [
        'whatsapp_reminder_days_before' => 'array',
        'whatsapp_reminder_days_after' => 'array',
        'whatsapp_reminder_on_due_date' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obter configurações padrão para novos tenants
     */
    public static function getDefaultSettings(): array
    {
        return [
            'whatsapp_reminder_days_before' => [3, 1],
            'whatsapp_reminder_on_due_date' => true,
            'whatsapp_reminder_days_after' => [1, 3, 7],
            'whatsapp_enabled' => true,
            'default_currency' => 'BRL',
            'default_due_days' => 7,
        ];
    }

    /**
     * Verificar se deve enviar lembrete para um tipo específico
     */
    public function shouldSendReminder(string $type, int $daysDiff): bool
    {
        if (!$this->whatsapp_enabled) {
            return false;
        }

        switch ($type) {
            case 'before':
                return in_array($daysDiff, $this->whatsapp_reminder_days_before ?? []);
            case 'on_due_date':
                return $this->whatsapp_reminder_on_due_date && $daysDiff === 0;
            case 'after':
                return in_array(abs($daysDiff), $this->whatsapp_reminder_days_after ?? []);
            default:
                return false;
        }
    }
}
