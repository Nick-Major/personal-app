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
        'work_date',
        'month_period',
        'start_time',
        'end_time', 
        'status', // ['scheduled', 'active', 'pending_approval', 'completed', 'paid', 'cancelled']
        'role',
        'notes',
        'worked_minutes',
        'specialty_id',
        'work_type_id',
        'address_id',
        'tax_status_id',
        'contract_type_id',
        'base_rate',
        'hand_amount',      // Сумма НА РУКИ (до налога)
        'payout_amount',    // Сумма К ВЫПЛАТЕ (с налогом)
        'tax_amount',       // Сумма налога
        'is_paid',
        'expenses_total',
        'compensation_amount',
        'compensation_description',
        'assignment_number' // ✅ ДОБАВЛЕНО - номер назначения для бригадиров
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_paid' => 'boolean',
        'compensation_amount' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'hand_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'expenses_total' => 'decimal:2',
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

    public function address()
    {
        return $this->belongsTo(Address::class);
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

    public function visitedLocations()
    {
        return $this->hasMany(VisitedLocation::class);
    }

    public function photos()
    {
        return $this->hasMany(ShiftPhoto::class);
    }

    public function compensations()
    {
        return $this->morphMany(Compensation::class, 'compensatable');
    }

    public function assignmentDate()
    {
        return $this->hasOne(BrigadierAssignmentDate::class, 'shift_id');
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

    public function scopeExecutor($query)
    {
        return $query->where('role', 'executor');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeByAssignmentNumber($query, $assignmentNumber)
    {
        return $query->where('assignment_number', $assignmentNumber);
    }

    // === МЕТОДЫ РАСЧЕТОВ ===

    /**
     * Определить базовую ставку для смены
     */
    public function determineBaseRate()
    {
        // 1. Если это наш исполнитель - берем ставку из user_specialties
        if ($this->user_id && $this->specialty_id) {
            $userSpecialty = $this->user->specialties()
                ->where('specialty_id', $this->specialty_id)
                ->first();
            
            return $userSpecialty->pivot->base_hourly_rate ?? $userSpecialty->base_hourly_rate ?? 0;
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
        
        return 0;
    }

    /**
     * Рассчитать сумму НА РУКИ (до налога)
     * Формула: (Базовая_ставка × Часы) + Компенсация + Операционные_расходы
     */
    public function calculateHandAmount()
    {
        $hours = $this->worked_minutes / 60;
        $baseRate = $this->base_rate ?: $this->determineBaseRate();
        $baseAmount = $baseRate * $hours;
        $compensation = $this->compensation_amount ?? 0;
        $expenses = $this->shiftExpenses->sum('amount');
        
        return $baseAmount + $compensation + $expenses;
    }

    /**
     * Рассчитать сумму налога
     */
    public function calculateTaxAmount()
    {
        $handAmount = $this->hand_amount ?: $this->calculateHandAmount();
        $taxRate = $this->taxStatus?->tax_rate ?? 0;
        
        return $handAmount * $taxRate;
    }

    /**
     * Рассчитать сумму К ВЫПЛАТЕ (с налогом)
     */
    public function calculatePayoutAmount()
    {
        $handAmount = $this->hand_amount ?: $this->calculateHandAmount();
        $taxAmount = $this->calculateTaxAmount();
        
        return $handAmount + $taxAmount;
    }

    /**
     * Обновить все расчеты смены
     */
    public function updateCalculations()
    {
        // Устанавливаем базовую ставку если не установлена
        if (!$this->base_rate) {
            $this->base_rate = $this->determineBaseRate();
        }

        // Устанавливаем month_period если не установлен
        if (!$this->month_period) {
            $this->month_period = $this->work_date->format('Y-m');
        }

        // Автоматически определяем tax_status и contract_type если не установлены
        if (!$this->tax_status_id) {
            $this->updateTaxStatus();
        }
        
        if (!$this->contract_type_id) {
            $this->updateContractType();
        }

        // Обновляем суммы по новой логике
        $this->hand_amount = $this->calculateHandAmount();     // НА РУКИ
        $this->tax_amount = $this->calculateTaxAmount();       // Налог
        $this->payout_amount = $this->calculatePayoutAmount(); // К ВЫПЛАТЕ
        $this->expenses_total = $this->shiftExpenses->sum('amount');
        
        $this->save();
        
        return $this;
    }

    // === WORKFLOW МЕТОДЫ ===
    public function startShift()
    {
        $this->update([
            'status' => 'active',
            'start_time' => now()
        ]);
    }

    public function endShift()
    {
        $this->update([
            'status' => 'pending_approval',
            'end_time' => now()
        ]);
    }

    public function submitForApproval()
    {
        $this->update(['status' => 'pending_approval']);
    }

    public function approve()
    {
        $this->update(['status' => 'completed']);
        $this->updateCalculations();
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'is_paid' => true
        ]);
    }

    // === СТАТУСНЫЕ МЕТОДЫ ===
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPendingApproval()
    {
        return $this->status === 'pending_approval';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isBrigadier()
    {
        return $this->role === 'brigadier';
    }

    public function isExecutor()
    {
        return $this->role === 'executor';
    }

    /**
     * Рассчитать общее время из посещенных локаций
     */
    public function calculateTotalTime()
    {
        $totalMinutes = $this->visitedLocations->sum('duration_minutes');
        $this->update(['worked_minutes' => $totalMinutes]);
        return $totalMinutes;
    }

    /**
     * Определить налоговый статус для смены
     */
    public function determineTaxStatus()
    {
        if ($this->tax_status_id) {
            return $this->taxStatus;
        }

        if ($this->user_id && $this->user->tax_status_id) {
            return $this->user->taxStatus;
        }
        
        if ($this->user_id && $this->user->contractor_id && $this->user->contractor->tax_status_id) {
            return $this->user->contractor->taxStatus;
        }
        
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
        if ($this->contract_type_id) {
            return $this->contractType;
        }

        if ($this->user_id && $this->user->contract_type_id) {
            return $this->user->contractType;
        }
        
        if ($this->user_id && $this->user->contractor_id && $this->user->contractor->contract_type_id) {
            return $this->user->contractor->contractType;
        }
        
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
