<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'premium_rate', // ДОБАВЛЯЕМ
        'is_active'
    ];

    protected $casts = [
        'premium_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Связи остаются без изменений
    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }
}
