<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'specialization',
        'is_contractor',
        'contractor_id',
        'notes',
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
        ];
    }

    // Связи
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
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

    public function grantedInitiatorRights()
    {
        return $this->hasMany(InitiatorGrant::class, 'brigadier_id');
    }

    public function givenInitiatorRights()
    {
        return $this->hasMany(InitiatorGrant::class, 'initiator_id');
    }
}
