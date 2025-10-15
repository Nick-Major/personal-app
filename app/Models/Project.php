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
        'default_payer_company',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function purposes()
    {
        return $this->hasMany(Purpose::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function payerCompanies()
    {
        return $this->hasManyThrough(PurposePayerCompany::class, Purpose::class);
    }

    public function addressRules()
    {
        return $this->hasManyThrough(PurposeAddressRule::class, Purpose::class);
    }
}
