<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'description',
        'type',
        'content',
        'variables',
        'is_default',
        'active',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_default' => 'boolean',
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function render(array $data = []): string
    {
        $content = $this->content;

        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }
}
