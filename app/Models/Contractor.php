<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',                    // Название компании
        'contact_person',          // ФИО контактного лица
        'contact_person_phone',    // Телефон контактного лица
        'contact_person_email',    // Email контактного лица
        'phone',                   // Основной телефон компании
        'email',                   // Основной email компании
        'user_id',                 // User-представитель компании
        'contract_type_id',        // ДОБАВИТЬ
        'tax_status_id',           // ДОБАВИТЬ
        'address',                 // Адрес компании
        'inn',                     // ИНН
        'bank_details',           // Банковские реквизиты
        'specializations',        // Специализации компании
        'notes',                  // Дополнительные заметки
        'is_active',              // Активен ли подрядчик
    ];

    protected $casts = [
        'specializations' => 'array',
        'is_active' => 'boolean',
    ];

    // === ОБНОВЛЕННЫЕ СВЯЗИ ===
    
    // User-представитель этой компании
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Персонализированные исполнители этой компании
    public function executors()
    {
        return $this->hasMany(User::class, 'contractor_id')
                    ->whereHas('roles', function($q) {
                        $q->where('name', 'executor');
                    });
    }

    // Заявки, где этот подрядчик участвует
    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class, 'contractor_id');
    }

    // Обезличенные смены подрядчика
    public function anonymousShifts()
    {
        return $this->hasMany(Shift::class)->whereNull('user_id');
    }

    // Все смены связанные с подрядчиком
    public function allShifts()
    {
        return Shift::where('contractor_id', $this->id)
                   ->orWhereHas('user', function($q) {
                       $q->where('contractor_id', $this->id);
                   });
    }

    // Добавляем в Contractor модель:
    public function contractorRates()
    {
        return $this->hasMany(ContractorRate::class);
    }

    // В модель Contractor добавляем:
    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
    }

    public function taxStatus()
    {
        return $this->belongsTo(TaxStatus::class);
    }

    // === МЕТОДЫ ===
    
    public function getTotalExecutorsCount()
    {
        return $this->executors()->count();
    }

    public function getActiveShiftsCount()
    {
        return $this->allShifts()->where('status', 'active')->count();
    }

    public function getCompletedShiftsThisMonth()
    {
        return $this->allShifts()
                   ->where('status', 'completed')
                   ->where('work_date', '>=', now()->startOfMonth())
                   ->count();
    }

    public function hasCategory($categoryId)
    {
        return $this->contractorRates()
            ->whereHas('specialty', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->where('is_active', true)
            ->exists();
    }
}
