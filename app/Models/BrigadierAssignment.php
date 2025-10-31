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
        'can_create_requests',
        'comment',
        'planned_address_id',        // ✅ ДОБАВИТЬ
        'planned_custom_address',    // ✅ ДОБАВИТЬ  
        'is_custom_planned_address', // ✅ ДОБАВИТЬ
    ];

    protected $casts = [
        'can_create_requests' => 'boolean',
        'is_custom_planned_address' => 'boolean', // ✅ ДОБАВИТЬ
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

    public function plannedAddress()
    {
        return $this->belongsTo(Address::class, 'planned_address_id');
    }

    public function assignment_dates(): HasMany
    {
        return $this->hasMany(BrigadierAssignmentDate::class, 'assignment_id');
    }

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

    // === SCOPES (ОБНОВЛЕННЫЕ) ===

    // Scope для назначений с подтвержденными датами
    public function scopeWithConfirmedDates($query)
    {
        return $query->whereHas('assignment_dates', function($q) {
            $q->where('status', 'confirmed');
        });
    }

    // Scope для назначений с ожидающими датами
    public function scopeWithPendingDates($query)
    {
        return $query->whereHas('assignment_dates', function($q) {
            $q->where('status', 'pending');
        });
    }
}
