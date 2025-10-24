<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MassPersonnelReport extends Model
{
    // app/Models/MassPersonnelReport.php
    protected $fillable = [
        'request_id',
        'brigadier_id', 
        'contractor_id',
        'specialty_id',
        'work_type_id',
        'work_date',
        'workers_count',
        'total_hours',
        'worker_names',
        'base_rate',
        'compensation_amount',
        'expenses_total', 
        'hand_amount',
        'payout_amount',
        'status',
        'is_paid',
        'notes'
    ];

    // Связи
    public function request() 
    { 
        return $this->belongsTo(WorkRequest::class); 
    }

    public function brigadier() 
    { 
        return $this->belongsTo(User::class, 'brigadier_id'); 
    }

    public function contractor() 
    { 
        return $this->belongsTo(Contractor::class); 
    }

    public function specialty() 
    { 
        return $this->belongsTo(Specialty::class); 
    }

    public function workType() 
    { 
        return $this->belongsTo(WorkType::class); 
    }

    public function compensation() 
    { 
        return $this->morphOne(Compensation::class, 'compensatable'); 
    }
}
