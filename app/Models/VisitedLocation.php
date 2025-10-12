<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitedLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'address',
        'latitude',
        'longitude',
        'started_at',
        'ended_at',
        'duration_minutes',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function photos()
    {
        return $this->hasMany(ShiftPhoto::class);
    }
}
