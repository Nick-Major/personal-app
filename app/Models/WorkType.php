<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Связи
    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function massPersonnelReports()
    {
        return $this->hasMany(MassPersonnelReport::class);
    }
}
