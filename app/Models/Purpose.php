<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'has_custom_payer_selection',
        'is_active'
    ];

    protected $casts = [
        'has_custom_payer_selection' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payerCompanies()
    {
        return $this->hasMany(PurposePayerCompany::class);
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
