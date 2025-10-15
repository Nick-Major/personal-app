<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function addressPrograms()
    {
        return $this->hasMany(AddressProgram::class);
    }

    // СВЯЗЬ ОДИН-КО-МНОГИМ
    public function purposes()
    {
        return $this->hasMany(Purpose::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function payerRules()
    {
        return $this->hasMany(PayerRule::class);
    }
}
