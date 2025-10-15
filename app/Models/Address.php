<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'full_address',
        'description'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
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
