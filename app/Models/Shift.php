<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'contractor_id',
        'contractor_worker_name',
        'work_date',
        'start_time',
        'end_time',
        'status',
        'shift_started_at',
        'shift_ended_at',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'shift_started_at' => 'datetime',
        'shift_ended_at' => 'datetime',
    ];

    // Связи
    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}
