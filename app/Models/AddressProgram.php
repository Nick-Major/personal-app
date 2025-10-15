<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'address_id',
        'order',
        'is_active'
    ];

    // Связи
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function payerRules()
    {
        return $this->hasMany(PayerRule::class);
    }
}
