<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayerRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'purpose_id',
        'address_id',
        'address_program_id',
        'project_id',
        'payer_company',
        'priority',
        'description',
        'is_custom' // true - если определяется индивидуально каждый раз
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'priority' => 'integer'
    ];

    // Связи
    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function addressProgram()
    {
        return $this->belongsTo(AddressProgram::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
