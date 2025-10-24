<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'surname',
        'patronymic',
        'email',
        'password',
        'phone',
        'telegram_id',
        'contractor_id',
        'contract_type_id', // ДОБАВИТЬ
        'tax_status_id',    // ДОБАВИТЬ
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // === ВИРТУАЛЬНЫЕ АТРИБУТЫ ДЛЯ FILAMENT ===
    
    /**
     * Accessor для типа исполнителя (для Filament)
     */
    public function getExecutorTypeAttribute()
    {
        if (!$this->hasRole('executor')) {
            return null;
        }
        
        return $this->contractor_id ? 'contractor' : 'our';
    }

    /**
     * Mutator для установки типа исполнителя
     */
    public function setExecutorTypeAttribute($value)
    {
        if ($value === 'our') {
            $this->contractor_id = null;
        }
        // Для 'contractor' contractor_id устанавливается через соответствующее поле
    }

    // === ОПРЕДЕЛЕНИЕ ТИПА ПОЛЬЗОВАТЕЛЯ ===
    
    public function isInitiator()
    {
        return $this->hasRole('initiator') && !$this->canHaveShifts();
    }
    
    public function isDispatcher() 
    {
        return $this->hasRole('dispatcher') && !$this->canHaveShifts();
    }
    
    // User-представитель подрядчика (управляет компанией)
    public function isExternalContractor()
    {
        return $this->hasRole('contractor') && is_null($this->contractor_id);
    }
    
    // Наш исполнитель (сотрудник компании)
    public function isOurExecutor()
    {
        return $this->hasRole('executor') && is_null($this->contractor_id);
    }
    
    // Персонализированный исполнитель подрядчика
    public function isContractorExecutor()
    {
        return $this->hasRole('executor') && !is_null($this->contractor_id);
    }

    /**
     * Получить тип пользователя для отображения
     */
    public function getUserTypeAttribute(): string
    {
        if ($this->isExternalContractor()) return '👑 Подрядчик';
        if ($this->isOurExecutor()) return '👷 Наш исполнитель';
        if ($this->isContractorExecutor()) return '🏢 Исполнитель подрядчика';
        if ($this->isInitiator()) return '📋 Инициатор';
        if ($this->isDispatcher()) return '📞 Диспетчер';
        return '❓ Другое';
    }
    
    // Может создавать заявки
    public function canCreateWorkRequests()
    {
        return $this->hasAnyRole(['initiator', 'dispatcher']);
    }
    
    // Может иметь смены (исполнитель)
    public function canHaveShifts()
    {
        return $this->hasRole('executor');
    }
    
    // Является ли бригадиром на указанную дату
    public function isBrigadier($date = null)
    {
        $date = $date ?: now();
        
        return $this->brigadierAssignments()
            ->whereHas('assignmentDates', function($q) use ($date) {
                $q->whereDate('assignment_date', $date)
                  ->where('status', 'confirmed');
            })
            ->exists();
    }

    // === СВЯЗИ ===
    // Для персонализированных исполнителей: компания-подрядчик
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    // Для user-подрядчиков: управляемая компания
    public function managedContractor()
    {
        return $this->hasOne(Contractor::class, 'user_id');
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'user_specialties')
                    ->withPivot('base_hourly_rate')
                    ->withTimestamps();
    }

    public function initiatedRequests()
    {
        return $this->hasMany(WorkRequest::class, 'initiator_id');
    }

    public function brigadierRequests()
    {
        return $this->hasMany(WorkRequest::class, 'brigadier_id');
    }

    public function dispatcherRequests()
    {
        return $this->hasMany(WorkRequest::class, 'dispatcher_id');
    }

    public function brigadierAssignments()
    {
        return $this->hasMany(BrigadierAssignment::class, 'brigadier_id');
    }

    public function initiatedBrigadierAssignments()
    {
        return $this->hasMany(BrigadierAssignment::class, 'initiator_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function grantedInitiatorRights()
    {
        return $this->hasMany(InitiatorGrant::class, 'brigadier_id');
    }

    public function givenInitiatorRights()
    {
        return $this->hasMany(InitiatorGrant::class, 'initiator_id');
    }

    // === СТАВКИ - НОВЫЕ СВЯЗИ ===
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // В модель User добавляем:
    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
    }

    public function taxStatus()
    {
        return $this->belongsTo(TaxStatus::class);
    }

    // === SCOPES ===
    public function scopeBrigadiers($query)
    {
        return $query->whereHas('brigadierAssignments', function($q) {
            $q->whereHas('assignmentDates', function($q) {
                $q->where('status', 'confirmed');
            });
        });
    }

    public function scopeOurExecutors($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'executor');
        })->whereNull('contractor_id');
    }

    public function scopeContractorExecutors($query, $contractorId = null)
    {
        $query = $query->whereHas('roles', function($q) {
            $q->where('name', 'executor');
        })->whereNotNull('contractor_id');
        
        if ($contractorId) {
            $query->where('contractor_id', $contractorId);
        }
        
        return $query;
    }

    public function scopeExternalContractors($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'contractor');
        })->whereNull('contractor_id');
    }

    public function scopeAvailable($query, $date)
    {
        return $query->whereDoesntHave('shifts', function($q) use ($date) {
            $q->whereDate('work_date', $date)
              ->whereIn('status', ['active', 'completed']);
        });
    }

    // === МЕТОДЫ ===
    public function getFullNameAttribute()
    {
        $parts = array_filter([$this->surname, $this->name, $this->patronymic]);
        return implode(' ', $parts) ?: $this->name;
    }

    public function canCreateRequestsAsBrigadier($date)
    {
        return $this->brigadierAssignments()
            ->whereHas('assignmentDates', function($q) use ($date) {
                $q->whereDate('assignment_date', $date)
                  ->where('status', 'confirmed');
            })
            ->where('can_create_requests', true)
            ->exists();
    }

    public function getExecutorRole($date = null)
    {
        $date = $date ?: now();
        
        if ($this->isBrigadier($date)) {
            return $this->canCreateRequestsAsBrigadier($date) 
                ? 'brigadier_with_rights' 
                : 'brigadier';
        }
        
        return 'executor';
    }

    public function getExecutorRoleDisplay($date = null)
    {
        $role = $this->getExecutorRole($date);
        $roles = [
            'executor' => 'Исполнитель',
            'brigadier' => 'Бригадир', 
            'brigadier_with_rights' => 'Бригадир (может создавать заявки)'
        ];
        return $roles[$role] ?? 'Исполнитель';
    }

    // Получить всех исполнителей (если это user-подрядчик)
    public function getManagedExecutors()
    {
        if (!$this->isExternalContractor()) {
            return collect();
        }
        
        return $this->managedContractor?->executors ?? collect();
    }

    // Получить все смены подрядчика
    public function getContractorShifts()
    {
        if (!$this->isExternalContractor()) {
            return collect();
        }
        
        return $this->managedContractor?->allShifts() ?? collect();
    }

    // === ВАЛИДАЦИЯ И БИЗНЕС-ЛОГИКА ===
    
    /**
     * Boot метод для валидации бизнес-правил
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            // Проверяем, что исполнитель привязан к подрядчику, если это исполнитель подрядчика
            if ($user->hasRole('executor') && $user->isContractorExecutor() && !$user->contractor_id) {
                throw new \Exception('Исполнитель подрядчика должен быть привязан к компании-подрядчику');
            }
            
            // Проверяем, что наш исполнитель не привязан к подрядчику
            if ($user->hasRole('executor') && $user->isOurExecutor() && $user->contractor_id) {
                throw new \Exception('Наш исполнитель не может быть привязан к подрядчику');
            }
            
            // Проверяем, что пользователь с ролью contractor не привязан к другому подрядчику
            if ($user->hasRole('contractor') && $user->contractor_id) {
                $existingContractor = Contractor::where('user_id', $user->id)->first();
                if ($existingContractor && $existingContractor->id != $user->contractor_id) {
                    throw new \Exception('Пользователь с ролью contractor уже привязан к другому подрядчику');
                }
            }
        });
    }

    // Обновляем метод getExecutorTypeInfo
    public function getExecutorTypeInfo(): array
    {
        if (!$this->hasRole('executor')) {
            return ['type' => 'not_executor', 'label' => 'Не исполнитель'];
        }

        if ($this->isOurExecutor()) {
            return [
                'type' => 'our',
                'label' => '👷 Наш исполнитель',
                'description' => 'Сотрудник компании',
                'contractor' => null,
                'contract_type' => $this->contractType?->name,
                'tax_status' => $this->taxStatus?->name
            ];
        }

        if ($this->isContractorExecutor()) {
            return [
                'type' => 'contractor',
                'label' => '🏢 Исполнитель подрядчика',
                'description' => 'Внешний специалист',
                'contractor' => $this->contractor,
                'contract_type' => $this->contractor?->contractType?->name,
                'tax_status' => $this->contractor?->taxStatus?->name
            ];
        }

        return ['type' => 'unknown', 'label' => 'Неизвестный тип'];
    }

    // === СТАВКИ - НОВЫЕ МЕТОДЫ ===

    /**
     * Получить ставку для специальности и вида работ с учетом приоритетов
     */
    public function getRateForSpecialtyAndWorkType($specialtyId, $workTypeId = null, $date = null)
    {
        $date = $date ?: now();
        
        // 1. Получаем базовую ставку пользователя для специальности
        $userSpecialty = $this->specialties()
            ->where('specialties.id', $specialtyId)
            ->first();
        
        if (!$userSpecialty) {
            return null; // Пользователь не имеет этой специальности
        }
        
        $baseRate = $userSpecialty->pivot->base_hourly_rate ?? $userSpecialty->base_hourly_rate;
        
        if (!$workTypeId) {
            return $baseRate; // Если вид работ не указан - возвращаем базовую ставку
        }
        
        // 2. Ищем ставки в порядке приоритета:
        $rates = Rate::where(function($query) use ($specialtyId, $workTypeId, $date) {
                $query->where('specialty_id', $specialtyId)
                      ->where('work_type_id', $workTypeId)
                      ->where(function($q) use ($date) {
                          $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date);
                      })
                      ->where(function($q) use ($date) {
                          $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
                      });
            })
            ->orderBy('user_id', 'desc') // Сначала индивидуальные ставки (user_id NOT NULL)
            ->orderBy('effective_from', 'desc') // Затем более новые ставки
            ->get();

        // 3. Применяем приоритеты:
        foreach ($rates as $rate) {
            // Приоритет 1: Индивидуальная ставка пользователя
            if ($rate->user_id === $this->id) {
                return $rate->hourly_rate;
            }
            
            // Приоритет 2: Базовая ставка специальности для вида работ
            if (!$rate->user_id) {
                return $rate->hourly_rate;
            }
        }
        
        // 4. Если не нашли специальных ставок - возвращаем базовую
        return $baseRate;
    }

    /**
     * Получить все доступные ставки пользователя
     */
    public function getAvailableRates($date = null)
    {
        $date = $date ?: now();
        
        return $this->specialties->mapWithKeys(function ($specialty) use ($date) {
            $baseRate = $specialty->pivot->base_hourly_rate ?? $specialty->base_hourly_rate;
            
            $workTypeRates = \App\Models\WorkType::all()->map(function ($workType) use ($specialty, $date) {
                return [
                    'work_type_id' => $workType->id,
                    'work_type_name' => $workType->name,
                    'rate' => $this->getRateForSpecialtyAndWorkType($specialty->id, $workType->id, $date),
                    'is_custom' => $this->hasCustomRateForSpecialtyAndWorkType($specialty->id, $workType->id, $date)
                ];
            });
            
            return [
                $specialty->name => [
                    'specialty_id' => $specialty->id,
                    'base_rate' => $baseRate,
                    'work_types' => $workTypeRates
                ]
            ];
        });
    }

    /**
     * Проверить есть ли индивидуальная ставка для специальности и вида работ
     */
    public function hasCustomRateForSpecialtyAndWorkType($specialtyId, $workTypeId, $date = null)
    {
        $date = $date ?: now();
        
        return Rate::where('user_id', $this->id)
            ->where('specialty_id', $specialtyId)
            ->where('work_type_id', $workTypeId)
            ->where(function($q) use ($date) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date);
            })
            ->where(function($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            })
            ->exists();
    }

    /**
     * Установить индивидуальную ставку для специальности и вида работ
     */
    public function setCustomRate($specialtyId, $workTypeId, $rate, $effectiveFrom = null, $effectiveTo = null)
    {
        return Rate::updateOrCreate(
            [
                'user_id' => $this->id,
                'specialty_id' => $specialtyId,
                'work_type_id' => $workTypeId,
            ],
            [
                'hourly_rate' => $rate,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
            ]
        );
    }
}
