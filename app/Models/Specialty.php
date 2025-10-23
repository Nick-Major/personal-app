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
        'category_id', // ИЗМЕНЕНО: было category (string), теперь category_id
        'base_hourly_rate',
        'is_active'
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // НОВАЯ СВЯЗЬ
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialties')
                    ->withPivot('base_hourly_rate', 'assigned_at', 'assigned_by')
                    ->withTimestamps();
    }

    // НОВАЯ СВЯЗЬ
    public function contractorRates()
    {
        return $this->hasMany(ContractorRate::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // УБИРАЕМ метод getRateWithPremium т.к. WorkType больше не участвует в расчетах
}
