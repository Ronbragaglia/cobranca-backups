<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'subdomain',
        'stripe_customer_id',
        'subscription_status',
        'evolution_api_key',
        'evolution_api_url',
        'evolution_instances',
        'qr_code_image',
        'custom_qr_enabled',
        'max_whatsapp_instances',
        'max_messages_per_month',
        'current_messages_month',
        'usage_reset_at',
        'active',
        'trial_ends_at',
    ];

    protected $casts = [
        'subscription_status' => 'string',
        'evolution_instances' => 'array',
        'custom_qr_enabled' => 'boolean',
        'usage_reset_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function cobrancas(): HasMany
    {
        return $this->hasMany(Cobranca::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(TenantSettings::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOnTrial($query)
    {
        return $query->where('trial_ends_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function canSendMessage(): bool
    {
        if ($this->subscription && $this->subscription->plan) {
            return $this->subscription->canSendMessage();
        }

        return $this->current_messages_month < $this->max_messages_per_month;
    }

    public function incrementMessages(): void
    {
        $this->increment('current_messages_month');
        
        if ($this->subscription) {
            $this->subscription->incrementMessages();
        }
    }

    public function resetUsage(): void
    {
        $this->update([
            'current_messages_month' => 0,
            'usage_reset_at' => now(),
        ]);

        if ($this->subscription) {
            $this->subscription->resetUsage();
        }
    }

    public function getPlan(): ?Plan
    {
        return $this->subscription ? $this->subscription->plan : null;
    }
}