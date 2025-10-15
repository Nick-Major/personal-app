<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', // ДОБАВЛЯЕМ
        'name',
        'description',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // ОБРАТНАЯ СВЯЗЬ
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payerRules()
    {
        return $this->hasMany(PayerRule::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }
}
