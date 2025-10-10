<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'phone',
        'email',
        'specializations',
        'is_active',
    ];

    protected $casts = [
        'specializations' => 'array',
        'is_active' => 'boolean',
    ];

    // Связи
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function workRequests()
    {
        return $this->hasManyThrough(WorkRequest::class, Shift::class);
    }
}
