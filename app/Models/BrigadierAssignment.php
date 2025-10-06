<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrigadierAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'brigadier_id',
        'initiator_id',
        'assignment_date',
        'status',
        'confirmed_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Связи
    public function brigadier()
    {
        return $this->belongsTo(User::class, 'brigadier_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class, 'brigadier_id', 'brigadier_id')
            ->whereDate('created_at', $this->assignment_date);
    }
}
