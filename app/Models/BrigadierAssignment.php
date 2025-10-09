<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrigadierAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'brigadier_id',
        'initiator_id',
    ];

    // Связи
    public function brigadier()
    {
        return $this->belongsTo(User::class, 'brigadier_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function assignmentDates()
    {
        // Явно указываем внешний ключ, т.к. в дочерней таблице он называется assignment_id
        return $this->hasMany(BrigadierAssignmentDate::class, 'assignment_id');
    }

    public function confirmedDates()
    {
        return $this->hasMany(BrigadierAssignmentDate::class, 'assignment_id')->where('status', 'confirmed');
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class, 'brigadier_id', 'brigadier_id');
    }
}
