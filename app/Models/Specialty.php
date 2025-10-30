<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'base_hourly_rate',
        'is_active'
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // === СВЯЗИ ===
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialties')
                    ->withTimestamps();
    }

    public function contractorRates()
    {
        return $this->hasMany(ContractorRate::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function massPersonnelReports()
    {
        return $this->hasMany(MassPersonnelReport::class);
    }

    // === БИЗНЕС-ЛОГИКА ===

    /**
     * Получить активные специальности
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить специальности по категории
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // === АКСЕССОРЫ ===

    /**
     * Полное название с категорией
     */
    public function getFullNameAttribute()
    {
        return $this->category ? $this->category->name . ' - ' . $this->name : $this->name;
    }

    /**
     * Отформатированная ставка
     */
    public function getFormattedRateAttribute()
    {
        return number_format($this->base_hourly_rate, 2) . ' руб/час';
    }
}
