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
        'selected_payer_company',
        'initiator_id',
        'brigadier_id',
        'category_id', // ИЗМЕНЕНО: было specialty_id
        'work_type_id',
        'executor_type',
        'executor_names', // НОВОЕ: ФИО исполнителей
        'total_worked_hours', // НОВОЕ: общее кол-во отработанных часов
        'workers_count',
        'estimated_shift_duration', // ИЗМЕНЕНО: было shift_duration
        'work_date',
        'start_time',
        'is_custom_payer',
        'additional_info', // ИЗМЕНЕНО: было comments
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
        'estimated_shift_duration' => 'decimal:2',
        'total_worked_hours' => 'decimal:2',
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

    // ИЗМЕНЕНО: добавляем связь с категорией
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Оставляем для обратной совместимости, но делаем nullable
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

    // === НОВЫЕ МЕТОДЫ ===
    
    /**
     * Получить отформатированные имена исполнителей
     */
    public function getFormattedExecutorNamesAttribute()
    {
        if (!$this->executor_names) {
            return 'Не указаны';
        }
        
        // Если это обезличенный персонал - возвращаем как есть
        if ($this->isAnonymousContractor()) {
            return $this->executor_names;
        }
        
        // Для персонализированных - форматируем список
        $names = array_filter(array_map('trim', explode(',', $this->executor_names)));
        return implode("\n", $names);
    }
    
    /**
     * Проверить, является ли заявка на обезличенный персонал подрядчика
     */
    public function isAnonymousContractor()
    {
        return $this->executor_type === 'contractor' && empty($this->executor_names);
    }
    
    /**
     * Обновить общее количество отработанных часов из смен
     */
    public function updateTotalWorkedHours()
    {
        $totalHours = $this->shifts()
            ->where('status', 'completed')
            ->get()
            ->sum(function ($shift) {
                return $shift->worked_minutes / 60; // Переводим минуты в часы
            });
            
        $this->update(['total_worked_hours' => $totalHours]);
        return $totalHours;
    }

    // === МЕТОД ДЛЯ ОПРЕДЕЛЕНИЯ ПЛАТЕЛЬЩИКА ===
    public function determinePayer()
    {
        // Если можно выбирать вручную - возвращаем выбранную
        if ($this->purpose && $this->purpose->has_custom_payer_selection && $this->selected_payer_company) {
            return $this->selected_payer_company;
        }

        // Ищем правило по адресу
        if ($this->address_id) {
            $rule = PurposeAddressRule::where('project_id', $this->project_id)
                ->where('purpose_id', $this->purpose_id)
                ->where('address_id', $this->address_id)
                ->first();
            
            if ($rule) return $rule->payer_company;
        }

        // Общее правило для назначения (без адреса)
        $generalRule = PurposeAddressRule::where('project_id', $this->project_id)
            ->where('purpose_id', $this->purpose_id)
            ->whereNull('address_id')
            ->first();

        return $generalRule?->payer_company;
    }
}
