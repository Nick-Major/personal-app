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
        return trim("{$this->surname} {$this->name} {$this->patronymic}");
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
}
