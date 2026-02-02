<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobrancaEnvio extends Model
{
    protected $fillable = ['cobranca_id', 'tipo', 'status', 'data', 'erro'];

    protected $casts = [
        'data' => 'datetime',
    ];

    public function cobranca()
    {
        return $this->belongsTo(Cobranca::class);
    }
}