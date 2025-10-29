<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRequest extends Model
{
    use HasFactory;

    // СТАТУСЫ ЗАЯВКИ
    const STATUS_PUBLISHED = 'published';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CLOSED = 'closed';
    const STATUS_NO_SHIFTS = 'no_shifts';
    const STATUS_WORKING = 'working';
    const STATUS_UNCLOSED = 'unclosed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // ТИПЫ ПЕРСОНАЛА
    const PERSONNEL_OUR = 'our';
    const PERSONNEL_CONTRACTOR = 'contractor';

    protected $fillable = [
        'request_number',
        'project_id',
        'purpose_id',
        'address_id',
        'initiator_id',
        'brigadier_id',
        'brigadier_manual',     // ВОССТАНОВЛЕНО
        'category_id',
        'work_type_id',
        'contractor_id',
        'personnel_type',       // ВОССТАНОВЛЕНО
        'mass_personnel_names',
        'total_worked_hours',
        'workers_count',
        'estimated_shift_duration',
        'work_date',
        'start_time',
        'custom_address',       // ВОССТАНОВЛЕНО
        'is_custom_address',    // ВОССТАНОВЛЕНО
        'additional_info',
        'status',               // ВОССТАНОВЛЕНО
        'dispatcher_id',
        'published_at',
        'staffed_at',
        'completed_at',
        'created_at',           // ВОССТАНОВЛЕНО
        'updated_at',           // ВОССТАНОВЛЕНО
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'published_at' => 'datetime',
        'staffed_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_custom_address' => 'boolean',
        'estimated_shift_duration' => 'decimal:2',
        'total_worked_hours' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

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

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'request_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function massPersonnelReports()
    {
        return $this->hasMany(MassPersonnelReport::class);
    }

    // === БИЗНЕС-МЕТОДЫ ===

    /**
     * Получить контактное лицо (бригадир или ручное)
     */
    public function getContactPersonAttribute()
    {
        return $this->brigadier_manual ?: $this->brigadier?->full_name;
    }

    /**
     * Получить финальный адрес (официальный или кастомный)
     */
    public function getFinalAddressAttribute()
    {
        if ($this->is_custom_address && $this->custom_address) {
            return $this->custom_address;
        }
        return $this->address?->full_address;
    }

    /**
     * Получить отображаемый тип персонала
     */
    public function getPersonnelTypeDisplayAttribute()
    {
        if ($this->personnel_type === self::PERSONNEL_OUR) {
            return 'Наш персонал';
        }
        if ($this->personnel_type === self::PERSONNEL_CONTRACTOR && $this->contractor) {
            return $this->contractor->name;
        }
        return 'Не указан';
    }

    /**
     * Проверить можно ли генерировать номер заявки
     */
    public function canGenerateRequestNumber()
    {
        return $this->status === self::STATUS_CLOSED && 
               $this->personnel_type && 
               $this->category_id;
    }

    /**
     * Сгенерировать номер заявки по правилам
     */
    public function generateRequestNumber()
    {
        if (!$this->canGenerateRequestNumber()) {
            return null;
        }

        if ($this->personnel_type === self::PERSONNEL_OUR) {
            return $this->category->prefix . '-' . $this->id . '/' . $this->work_date->year;
        }

        if ($this->personnel_type === self::PERSONNEL_CONTRACTOR && $this->contractor) {
            return $this->contractor->contractor_code . '-' . $this->id . '/' . $this->work_date->year;
        }

        return null;
    }
}
