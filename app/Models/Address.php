<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_address', 
        'description'
        // Убираем project_id из fillable
    ];

    // Меняем belongsTo на belongsToMany
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    // Остальные связи остаются без изменений
    public function addressRules()
    {
        return $this->hasMany(PurposeAddressRule::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }
}
