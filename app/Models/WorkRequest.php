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
        'specialty_id',
        'work_type_id',
        'executor_type', // 'our_staff', 'contractor'
        'workers_count',
        'shift_duration',
        'work_date', // ДОБАВЛЕНО - дата выполнения работ
        'project',
        'purpose',
        'payer_company',
        'comments',
        'status', // 'draft', 'published', 'in_progress', 'staffed', 'completed'
        'dispatcher_id',
        'published_at',
        'staffed_at',
        'completed_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'published_at' => 'datetime',
        'staffed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // === СВЯЗИ ===
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

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'request_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function brigadierAssignmentDate()
    {
        return $this->hasOneThrough(
            BrigadierAssignmentDate::class,
            BrigadierAssignment::class,
            'brigadier_id', // Внешний ключ в BrigadierAssignment
            'assignment_id', // Внешний ключ в BrigadierAssignmentDate
            'brigadier_id', // Локальный ключ в WorkRequest
            'id' // Локальный ключ в BrigadierAssignment
        )->whereDate('assignment_date', $this->work_date);
    }
}
