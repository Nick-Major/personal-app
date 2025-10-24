<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id', // Номер заявки
        'user_id', // ID пользователя
        'contractor_id', // ID подрядчика
        'contractor_worker_name', // Кто это???
        'work_date', // Дата смены
        'start_time', // Время начала смены
        'end_time', // Время окончания смены
        'status', // Статус смены
        'role', // Роль в смене: Исполнитель или бригадир
        'shift_started_at', // Время начала смены - дубль с start_time?
        'shift_ended_at', // Время окончания смены - дубль с end_time?
        'notes', // Заметки
        'worked_minutes', // Рабочие часы
        'lunch_minutes', // время обеда - зачем???
        'travel_expense_amount', // Сумма транспортных расходов - зачем???
        'specialty_id', // Специальность
        'work_type_id', // Тип работ
        'tax_status_id', // Налоговый режим
        'contract_type_id',     // Тип договора
        'hourly_rate_snapshot', // Что это???
        'gross_amount',         // переименовать в gross_amount
        'expenses_total', // Общая сумма расходов?
        'grand_total', // Что это?
        'is_paid',              // ДОБАВЛЕНО
        'amount_to_pay',        // ДОБАВЛЕНО
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'shift_started_at' => 'datetime',
        'shift_ended_at' => 'datetime',
        'is_paid' => 'boolean',
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

    public function taxStatus()
    {
        return $this->belongsTo(TaxStatus::class);
    }

    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
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

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
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

    // === НОВАЯ ФОРМУЛА РАСЧЕТОВ ===

    /**
     * Определить часовую ставку для смены
     */
    public function determineHourlyRate()
    {
        // 1. Если это наш исполнитель - берем ставку из user_specialties
        if ($this->user_id && $this->specialty_id) {
            $userSpecialty = $this->user->specialties()
                ->where('specialty_id', $this->specialty_id)
                ->first();
            
            if ($userSpecialty) {
                return $userSpecialty->pivot->base_hourly_rate ?? $userSpecialty->base_hourly_rate;
            }
        }
        
        // 2. Если это персонализированный исполнитель подрядчика
        if ($this->user_id && $this->user->contractor_id && $this->specialty_id) {
            $contractorRate = ContractorRate::where('contractor_id', $this->user->contractor_id)
                ->where('specialty_id', $this->specialty_id)
                ->where('is_anonymous', false)
                ->where('is_active', true)
                ->first();
                
            return $contractorRate?->hourly_rate ?? 0;
        }
        
        // 3. Если это обезличенный персонал подрядчика
        if ($this->contractor_id && !$this->user_id && $this->specialty_id) {
            $contractorRate = ContractorRate::where('contractor_id', $this->contractor_id)
                ->where('specialty_id', $this->specialty_id)
                ->where('is_anonymous', true)
                ->where('is_active', true)
                ->first();
                
            return $contractorRate?->hourly_rate ?? 0;
        }
        
        return 0;
    }

    /**
     * Общая сумма на руки (до вычета налогов)
     */
    public function getGrossAmountAttribute()
    {
        $hours = $this->worked_minutes / 60;
        $rate = $this->hourly_rate_snapshot ?: $this->determineHourlyRate();
        
        return $hours * $rate;
    }

    /**
     * Сумма налога
     */
    public function getTaxAmountAttribute()
    {
        $grossAmount = $this->gross_amount;
        $taxRate = $this->taxStatus?->tax_rate ?? 0;
        
        return $grossAmount * $taxRate;
    }

    /**
     * Сумма к оплате (после вычета налогов)
     */
    public function getAmountToPayAttribute()
    {
        $grossAmount = $this->gross_amount;
        $taxAmount = $this->tax_amount;
        $expenses = $this->shiftExpenses->sum('amount');
        
        return ($grossAmount - $taxAmount) + $expenses;
    }

    /**
     * Сумма операционных расходов
     */
    public function getExpensesAmountAttribute()
    {
        return $this->shiftExpenses->sum('amount');
    }

    /**
     * Месяц смены (для отчетности)
     */
    public function getMonthAttribute()
    {
        return $this->work_date->format('Y-m');
    }

    /**
     * Компания-плательщик (из заявки)
     */
    public function getPayerCompanyAttribute()
    {
        return $this->workRequest?->determinePayer();
    }

    /**
     * Обновить все расчеты смены
     */
    public function updateCalculations()
    {
        // Определяем ставку если не установлена
        if (!$this->hourly_rate_snapshot) {
            $this->hourly_rate_snapshot = $this->determineHourlyRate();
        }
        
        // Определяем налоговый статус если не установлен
        if (!$this->tax_status_id) {
            $this->updateTaxStatus();
        }
        
        // Определяем тип договора если не установлен
        if (!$this->contract_type_id) {
            $this->updateContractType();
        }
        
        // Сохраняем обновленные данные
        $this->save();
        
        return $this;
    }

    /**
     * Определить налоговый статус для смены
     */
    public function determineTaxStatus()
    {
        // Если уже установлен - используем его
        if ($this->tax_status_id) {
            return $this->taxStatus;
        }

        // 1. Если это наш исполнитель - берем его налоговый статус
        if ($this->user_id && $this->user->tax_status_id) {
            return $this->user->taxStatus;
        }
        
        // 2. Если это персонализированный исполнитель подрядчика
        if ($this->user_id && $this->user->contractor_id && $this->user->contractor->tax_status_id) {
            return $this->user->contractor->taxStatus;
        }
        
        // 3. Если это обезличенный персонал подрядчика
        if ($this->contractor_id && !$this->user_id && $this->contractor->tax_status_id) {
            return $this->contractor->taxStatus;
        }
        
        return null;
    }

    /**
     * Обновить налоговый статус смены
     */
    public function updateTaxStatus()
    {
        $taxStatus = $this->determineTaxStatus();
        if ($taxStatus) {
            $this->tax_status_id = $taxStatus->id;
        }
        return $taxStatus;
    }

    /**
     * Определить тип договора для смены
     */
    public function determineContractType()
    {
        // Если уже установлен - используем его
        if ($this->contract_type_id) {
            return $this->contractType;
        }

        // 1. Если это наш исполнитель - берем его тип договора
        if ($this->user_id && $this->user->contract_type_id) {
            return $this->user->contractType;
        }
        
        // 2. Если это персонализированный исполнитель подрядчика
        if ($this->user_id && $this->user->contractor_id && $this->user->contractor->contract_type_id) {
            return $this->user->contractor->contractType;
        }
        
        // 3. Если это обезличенный персонал подрядчика
        if ($this->contractor_id && !$this->user_id && $this->contractor->contract_type_id) {
            return $this->contractor->contractType;
        }
        
        return null;
    }

    /**
     * Обновить тип договора смены
     */
    public function updateContractType()
    {
        $contractType = $this->determineContractType();
        if ($contractType) {
            $this->contract_type_id = $contractType->id;
        }
        return $contractType;
    }
}

