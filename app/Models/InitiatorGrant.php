<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InitiatorGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'initiator_id',
        'brigadier_id',
        'is_temporary',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_temporary' => 'boolean',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    // Связи
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function brigadier()
    {
        return $this->belongsTo(User::class, 'brigadier_id');
    }
}
