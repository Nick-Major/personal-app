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

    public function shiftExpenses()
    {
        return $this->hasMany(ShiftExpense::class);
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

    // === МЕТОДЫ ДЛЯ НОВОЙ СТРУКТУРЫ РАСЧЕТОВ ===

    /**
     * Расчет базовой суммы (ставка + надбавка) × часы
     */
    public function getBaseAmountAttribute()
    {
        $hours = $this->worked_minutes / 60; // Переводим минуты в часы
        $rate = $this->base_rate + ($this->workType->premium_rate ?? 0);
        return $rate * $hours;
    }

    /**
     * Расчет бонуса за отсутствие обеда
     */
    public function getNoLunchBonusAttribute()
    {
        if (!$this->no_lunch) {
            return 0;
        }
        
        $settings = \App\Models\ShiftSetting::first();
        $bonusHours = $settings ? $settings->no_lunch_bonus_hours : 1;
        $rate = $this->base_rate + ($this->workType->premium_rate ?? 0);
        
        return $rate * $bonusHours;
    }

    /**
     * Расчет транспортной надбавки
     */
    public function getTransportFeeAmountAttribute()
    {
        if (!$this->has_transport_fee) {
            return 0;
        }
        
        $settings = \App\Models\ShiftSetting::first();
        return $settings ? $settings->transport_fee : 0;
    }

    /**
     * Сумма операционных расходов
     */
    public function getExpensesAmountAttribute()
    {
        return $this->shiftExpenses->sum('amount');
    }

    /**
     * Итоговая сумма к выплате
     */
    public function getCalculatedTotalAttribute()
    {
        return $this->base_amount 
             + $this->no_lunch_bonus 
             + $this->transport_fee_amount 
             + $this->expenses_amount;
    }
}

