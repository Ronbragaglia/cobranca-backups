<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_status',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'current_messages_month',
        'current_whatsapp_instances',
        'usage_reset_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'usage_reset_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trialing');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'trialing';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function canSendMessage(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->plan->unlimited_messages) {
            return true;
        }

        return $this->current_messages_month < $this->plan->max_messages_per_month;
    }

    public function incrementMessages(): void
    {
        $this->increment('current_messages_month');
    }

    public function resetUsage(): void
    {
        $this->update([
            'current_messages_month' => 0,
            'usage_reset_at' => now(),
        ]);
    }
}
