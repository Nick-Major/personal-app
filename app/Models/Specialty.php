<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'name', 
        'description',
        'base_hourly_rate',
        'is_active'
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialties')
                    ->withPivot('base_hourly_rate')
                    ->withTimestamps();
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // Получить базовые ставки по видам работ (без привязки к пользователю)
    public function getWorkTypeRates($workTypeId = null)
    {
        $query = $this->rates()
            ->whereNull('user_id')
            ->whereNotNull('work_type_id');
            
        if ($workTypeId) {
            $query->where('work_type_id', $workTypeId);
        }
            
        return $query->get();
    }

    // === СТАВКИ - НОВЫЕ МЕТОДЫ ===

    /**
     * Получить базовые ставки специальности по видам работ с учетом даты действия
     */
    public function getBaseWorkTypeRates($workTypeId = null, $date = null)
    {
        $date = $date ?: now();
        
        $query = $this->rates()
            ->whereNull('user_id')
            ->whereNotNull('work_type_id')
            ->where(function($q) use ($date) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date);
            })
            ->where(function($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            });
            
        if ($workTypeId) {
            $query->where('work_type_id', $workTypeId);
        }
            
        return $query->get();
    }

    /**
     * Установить базовую ставку специальности для вида работ
     */
    public function setBaseWorkTypeRate($workTypeId, $rate, $effectiveFrom = null, $effectiveTo = null)
    {
        return Rate::updateOrCreate(
            [
                'specialty_id' => $this->id,
                'work_type_id' => $workTypeId,
                'user_id' => null, // Базовая ставка
            ],
            [
                'hourly_rate' => $rate,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
            ]
        );
    }

    /**
     * Получить ставку для вида работ с учетом даты
     */
    public function getRateForWorkType($workTypeId, $date = null)
    {
        $date = $date ?: now();
        
        $rate = $this->rates()
            ->whereNull('user_id')
            ->where('work_type_id', $workTypeId)
            ->where(function($q) use ($date) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date);
            })
            ->where(function($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            })
            ->first();
            
        return $rate ? $rate->hourly_rate : $this->base_hourly_rate;
    }
}


