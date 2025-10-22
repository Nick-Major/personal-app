<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_name',     // было 'name'
        'full_address', 
        'location_type'   // было 'description'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function addressRules()
    {
        return $this->hasMany(PurposeAddressRule::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }
}
