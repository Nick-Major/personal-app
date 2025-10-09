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
        return $this->hasMany(BrigadierAssignmentDate::class);
    }

    public function confirmedDates()
    {
        return $this->hasMany(BrigadierAssignmentDate::class)->where('status', 'confirmed');
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class, 'brigadier_id', 'brigadier_id');
    }
}
