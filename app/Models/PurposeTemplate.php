<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active'
        // Убираем description и настройки оплаты
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function createPurposeForProject(Project $project): Purpose
    {
        return Purpose::create([
            'project_id' => $project->id,
            'name' => $this->name,
            'description' => null, // Описание будет заполняться в проекте
            'payer_selection_type' => 'strict', // По умолчанию строгая
            'default_payer_company' => null, // Настраивается в проекте
            'has_custom_payer_selection' => false,
            'is_active' => true,
        ]);
    }
}
