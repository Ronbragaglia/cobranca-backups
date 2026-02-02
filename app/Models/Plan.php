<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'interval',
        'stripe_price_id',
        'max_whatsapp_instances',
        'max_messages_per_month',
        'unlimited_messages',
        'api_access',
        'custom_qr',
        'analytics',
        'priority_support',
        'features',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'active' => 'boolean',
        'unlimited_messages' => 'boolean',
        'api_access' => 'boolean',
        'custom_qr' => 'boolean',
        'analytics' => 'boolean',
        'priority_support' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    public function getIntervalLabelAttribute(): string
    {
        return $this->interval === 1 ? 'Mensal' : $this->interval . ' meses';
    }
}
