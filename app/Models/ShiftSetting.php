<?php
// app/Models/ShiftSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_fee',
        'no_lunch_bonus_hours'
    ];

    protected $casts = [
        'transport_fee' => 'decimal:2',
    ];
}
