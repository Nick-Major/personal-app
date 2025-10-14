<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrigadierAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'brigadier_id',
        'initiator_id',
        'can_create_requests', // ДОБАВЛЕНО - права Инициатора-Бригадира
        'status', // 'pending', 'confirmed', 'rejected'
        'confirmed_at',
        'rejected_at', 
        'rejection_reason'
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'can_create_requests' => 'boolean',
    ];

    // === СВЯЗИ ===
    
    public function brigadier()
    {
        return $this->belongsTo(User::class, 'brigadier_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    // ИСПРАВЛЕНО: правильное имя отношения для Filament
    public function assignment_dates(): HasMany
    {
        return $this->hasMany(BrigadierAssignmentDate::class, 'assignment_id');
    }

    // Alias для обратной совместимости
    public function assignmentDates()
    {
        return $this->assignment_dates();
    }

    public function confirmedDates()
    {
        return $this->hasMany(BrigadierAssignmentDate::class, 'assignment_id')
                    ->where('status', 'confirmed');
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class, 'brigadier_id', 'brigadier_id');
    }

    // Scope для подтвержденных назначений
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // Scope для ожидающих подтверждения  
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope для отклоненных
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
