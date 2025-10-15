<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'description',
        'coordinates'
    ];

    public function addressPrograms()
    {
        return $this->hasMany(AddressProgram::class);
    }

    public function payerRules()
    {
        return $this->hasMany(PayerRule::class);
    }
}
