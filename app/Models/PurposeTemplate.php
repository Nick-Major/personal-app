<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'is_active',
        'default_payer_selection_type',
        'default_payer_company'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Метод для создания назначения из шаблона
    public function createPurposeForProject(Project $project): Purpose
    {
        return Purpose::create([
            'project_id' => $project->id,
            'name' => $this->name,
            'description' => $this->description,
            'payer_selection_type' => $this->default_payer_selection_type,
            'default_payer_company' => $this->default_payer_company,
            'has_custom_payer_selection' => false,
            'is_active' => true,
        ]);
    }
}
