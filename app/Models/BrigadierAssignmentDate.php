<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrigadierAssignmentDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'assignment_date',
        'status',
        'confirmed_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(BrigadierAssignment::class);
    }

    public function brigadier()
    {
        return $this->hasOneThrough(User::class, BrigadierAssignment::class, 'id', 'id', 'assignment_id', 'brigadier_id');
    }
}
