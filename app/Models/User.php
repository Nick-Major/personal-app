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
        'is_contractor',
        'contractor_id',
        'notes',
        'is_always_brigadier', // для спецов, которые всегда бригадиры
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_contractor' => 'boolean',
            'is_always_brigadier' => 'boolean',
        ];
    }

    // === СВЯЗИ ===
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
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

    // === SCOPES ===
    public function scopeBrigadiers($query)
    {
        return $query->whereHas('brigadierAssignments', function($q) {
            $q->whereHas('assignmentDates', function($q) {
                $q->where('status', 'confirmed');
            });
        })->orWhere('is_always_brigadier', true);
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

    public function canCreateRequests($date)
    {
        return $this->brigadierAssignments()
            ->whereHas('assignmentDates', function($q) use ($date) {
                $q->whereDate('assignment_date', $date)
                  ->where('status', 'confirmed');
            })
            ->where('can_create_requests', true)
            ->exists();
    }

    // Определить текущую роль для ЛК
    public function getExecutorRole($date = null)
    {
        $date = $date ?: now();
        
        // Если есть подтвержденные назначения бригадиром на дату
        $isBrigadier = $this->brigadierAssignments()
            ->whereHas('assignmentDates', function($q) use ($date) {
                $q->whereDate('assignment_date', $date)
                  ->where('status', 'confirmed');
            })
            ->exists();
            
        if ($isBrigadier) {
            $canCreate = $this->brigadierAssignments()
                ->whereHas('assignmentDates', function($q) use ($date) {
                    $q->whereDate('assignment_date', $date)
                      ->where('status', 'confirmed');
                })
                ->where('can_create_requests', true)
                ->exists();
                
            return $canCreate ? 'brigadier_with_rights' : 'brigadier';
        }
        
        return 'executor';
    }

    // Получить отображаемое имя роли
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
