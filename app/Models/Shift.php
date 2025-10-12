<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'contractor_id',
        'contractor_worker_name',
        'work_date',
        'start_time',
        'end_time',
        'status', // 'planned', 'active', 'completed', 'cancelled'
        'role', // 'executor', 'brigadier' - ДОБАВЛЕНО
        'shift_started_at',
        'shift_ended_at',
        'notes',
        'worked_minutes',
        'lunch_minutes',
        'travel_expense_amount',
        'specialty_id',
        'work_type_id',
        'hourly_rate_snapshot',
        'total_amount',
        'expenses_total',
        'grand_total',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'shift_started_at' => 'datetime',
        'shift_ended_at' => 'datetime',
    ];

    // === СВЯЗИ ===
    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function segments()
    {
        return $this->hasMany(ShiftSegment::class);
    }

    public function visitedLocations()
    {
        return $this->hasMany(VisitedLocation::class);
    }

    public function photos()
    {
        return $this->hasMany(ShiftPhoto::class);
    }

    // === SCOPES ===
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('work_date', today());
    }

    public function scopeBrigadier($query)
    {
        return $query->where('role', 'brigadier');
    }

    // === МЕТОДЫ ===
    public function isBrigadier()
    {
        return $this->role === 'brigadier';
    }

    public function calculateTotalTime()
    {
        $totalMinutes = $this->visitedLocations->sum('duration_minutes');
        $this->update(['worked_minutes' => $totalMinutes]);
        return $totalMinutes;
    }
}

