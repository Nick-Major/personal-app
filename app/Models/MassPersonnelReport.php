<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassPersonnelReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'workers_count',
        'total_hours', 
        'worker_names',
        'compensation_amount',
        'compensation_description',
        'tax_status_id',
        'contract_type_id',
        'category_id', 
        'specialty_id', // ← ДОБАВЛЕНО
        'work_type_id',
        'base_hourly_rate',
        'total_amount',
        'expenses_total',
        'tax_amount',
        'net_amount',
        'status',
        'submitted_at',
        'approved_at', 
        'paid_at'
    ];

    protected $casts = [
        'total_hours' => 'decimal:2',
        'compensation_amount' => 'decimal:2',
        'base_hourly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expenses_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // === СВЯЗИ ===
    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'request_id');
    }

    public function taxStatus()
    {
        return $this->belongsTo(TaxStatus::class);
    }

    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    // Посещенные локации (аналогично Shift)
    public function visitedLocations()
    {
        return $this->hasMany(MassPersonnelVisitedLocation::class);
    }

    public function compensations()
    {
        return $this->morphMany(Compensation::class, 'compensatable');
    }

    // === РАСЧЕТНЫЕ МЕТОДЫ ===

    /**
     * Рассчитать общую сумму
     */
    public function calculateTotalAmount()
    {
        $baseAmount = $this->base_hourly_rate * $this->total_hours;
        $compensation = $this->compensation_amount;
        $expenses = $this->expenses_total;
        
        return $baseAmount + $compensation + $expenses;
    }

    /**
     * Рассчитать сумму налога
     */
    public function calculateTaxAmount()
    {
        $totalAmount = $this->total_amount ?: $this->calculateTotalAmount();
        $taxRate = $this->taxStatus?->tax_rate ?? 0;
        
        return $totalAmount * $taxRate;
    }

    /**
     * Рассчитать чистую сумму (к выплате)
     */
    public function calculateNetAmount()
    {
        $totalAmount = $this->total_amount ?: $this->calculateTotalAmount();
        $taxAmount = $this->tax_amount ?: $this->calculateTaxAmount();
        
        return $totalAmount - $taxAmount;
    }

    /**
     * Обновить все расчеты
     */
    public function updateCalculations()
    {
        $this->total_amount = $this->calculateTotalAmount();
        $this->tax_amount = $this->calculateTaxAmount();
        $this->net_amount = $this->calculateNetAmount();
        
        $this->save();
    }

    /**
     * Получить общее время из посещенных локаций
     */
    public function getTotalTimeFromLocations()
    {
        return $this->visitedLocations()->sum('duration_minutes') / 60;
    }

    // === СТАТУСНЫЕ МЕТОДЫ ===

    public function submitForApproval()
    {
        $this->update([
            'status' => 'pending_approval',
            'submitted_at' => now()
        ]);
    }

    public function approve()
    {
        $this->update([
            'status' => 'approved', 
            'approved_at' => now()
        ]);
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }
}
