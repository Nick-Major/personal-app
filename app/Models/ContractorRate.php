<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_id',
        'specialty_id',
        'hourly_rate', 
        'is_anonymous',
        'is_active'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
