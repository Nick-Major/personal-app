<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'name', 
        'description',
        'category',
        'requires_special_equipment',
        'is_active',
        'default_duration_hours',
        'complexity_level'
    ];

    protected $casts = [
        'requires_special_equipment' => 'boolean',
        'is_active' => 'boolean',
        'default_duration_hours' => 'decimal:2',
        'complexity_level' => 'integer',
    ];

    // ДОБАВЛЯЕМ СВЯЗЬ С ЗАЯВКАМИ
    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }
}
