<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    protected $fillable = ['descricao', 'valor', 'status', 'data_vencimento', 'telefone', 'tenant_id', 'notificacao_whatsapp_status', 'ultimo_envio_whatsapp', 'stripe_customer_id', 'whatsapp_reminders_sent', 'stripe_payment_link'];

    protected $casts = [
        'data_vencimento' => 'date',
        'ultimo_envio_whatsapp' => 'datetime',
        'whatsapp_reminders_sent' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
