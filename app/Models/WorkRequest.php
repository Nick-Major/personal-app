<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'request_number',
    'project_id',
    'purpose_id', 
    'address_id',
    'selected_payer_company', // НОВОЕ ПОЛЕ
    'initiator_id',
    'brigadier_id',
    'specialty_id',
    'work_type_id',
    'executor_type',
    'workers_count',
    'shift_duration',
    'work_date',
    'start_time',
    // 'payer_company', // УДАЛЯЕМ СТАРОЕ ПОЛЕ
    'is_custom_payer',
    'comments',
    'status',
    'dispatcher_id',
    'published_at',
    'staffed_at',
    'completed_at',
];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'published_at' => 'datetime',
        'staffed_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_custom_payer' => 'boolean',
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

    // === НОВЫЕ СВЯЗИ ===
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // === СУЩЕСТВУЮЩИЕ СВЯЗИ ===
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
            'brigadier_id',
            'assignment_id',
            'brigadier_id',
            'id'
        )->whereDate('assignment_date', $this->work_date);
    }

    // === МЕТОД ДЛЯ ОПРЕДЕЛЕНИЯ ПЛАТЕЛЬЩИКА ===
    public function determinePayer()
    {
        if ($this->purpose && $this->purpose->has_custom_payer_selection && $this->selected_payer_company) {
            return $this->selected_payer_company;
        }

        // Ищем правило по приоритету
        $rule = \App\Models\PayerRule::where('purpose_id', $this->purpose_id)
            ->where(function($query) {
                $query->where('address_id', $this->address_id)
                      ->orWhereHas('addressProgram', function($q) {
                          $q->where('address_id', $this->address_id)
                            ->where('project_id', $this->project_id);
                      })
                      ->orWhere('project_id', $this->project_id);
            })
            ->orderBy('priority', 'asc')
            ->first();

        return $rule?->payer_company;
    }
}
