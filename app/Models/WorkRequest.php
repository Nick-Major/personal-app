<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'initiator_id',
        'brigadier_id',
        'specialization',
        'executor_type',
        'workers_count',
        'shift_duration',
        'project',
        'purpose',
        'payer_company',
        'comments',
        'status',
        'dispatcher_id',
        'published_at',
        'staffed_at',
        'completed_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'staffed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Связи
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function brigadier()
    {
        return $this->belongsTo(User::class, 'brigadier_id');
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function brigadierAssignment()
    {
        return $this->hasOne(BrigadierAssignment::class, 'brigadier_id', 'brigadier_id')
            ->whereDate('assignment_date', $this->created_at->toDateString());
    }
}
