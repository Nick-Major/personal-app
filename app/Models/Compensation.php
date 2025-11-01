<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compensation extends Model
{
    protected $table = 'compensations'; // Явно указываем имя таблицы
    
    protected $fillable = [
        'description',
        'requested_amount',
        'approved_amount',
        'status',
        'approved_by',
        'approval_notes',
        'approved_at'
    ];

    // Полиморфная связь
    public function compensatable()
    {
        return $this->morphTo();
    }

    // Связь с утверждающим
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
