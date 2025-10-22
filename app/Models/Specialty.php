<?php
// app/Models/Specialty.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description',
        'category', // ДОБАВЛЕНО
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
                    ->withTimestamps();
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function getRateWithPremium($workType = null)
    {
        $baseRate = $this->base_hourly_rate;
        
        if ($workType && $workType->premium_rate > 0) {
            return $baseRate + $workType->premium_rate;
        }
        
        return $baseRate;
    }
}


