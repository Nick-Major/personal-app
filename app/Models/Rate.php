<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty_id', 
        'work_type_id',
        'hourly_rate',
        'effective_from',
        'effective_to'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    // === СВЯЗИ ===
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    // === МЕТОДЫ ===
    
    /**
     * Проверяет действует ли ставка на указанную дату
     */
    public function isEffectiveOn($date = null)
    {
        $date = $date ?: now();
        
        $fromValid = !$this->effective_from || $this->effective_from <= $date;
        $toValid = !$this->effective_to || $this->effective_to >= $date;
        
        return $fromValid && $toValid;
    }

    /**
     * Получить отображаемое название ставки
     */
    public function getDisplayNameAttribute()
    {
        if ($this->user_id && $this->work_type_id) {
            return "Индивидуальная ({$this->user->name} - {$this->workType->name})";
        }
        
        if ($this->user_id) {
            return "Индивидуальная ({$this->user->name})";
        }
        
        if ($this->work_type_id) {
            return "Базовая ({$this->workType->name})";
        }
        
        return "Базовая специальности";
    }
}
