<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'specialty_id',
        'work_type_id',
        'minutes',
        'hourly_rate_snapshot',
        'amount',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }
}


