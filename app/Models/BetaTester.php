<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetaTester extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'segment',
        'status',
        'invited_at',
        'accepted_at',
        'discount_percentage',
        'notes',
        'feedback_score',
        'referrals_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'discount_percentage' => 'integer',
        'feedback_score' => 'integer',
        'referrals_count' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_INVITED = 'invited';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_INVITED => 'Convidado',
            self::STATUS_ACCEPTED => 'Aceito',
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
        ];
    }

    /**
     * Check if beta tester is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if beta tester has been invited
     */
    public function isInvited(): bool
    {
        return in_array($this->status, [
            self::STATUS_INVITED,
            self::STATUS_ACCEPTED,
            self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Scope to get only active beta testers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get only pending beta testers
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get only invited beta testers
     */
    public function scopeInvited($query)
    {
        return $query->where('status', self::STATUS_INVITED);
    }
}
