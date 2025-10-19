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
        'category',
        'base_hourly_rate',
        'is_active'
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ДОБАВЛЯЕМ СВЯЗЬ С ПОЛЬЗОВАТЕЛЯМИ (many-to-many)
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialties');
    }
}


