<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposePayerCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', // ДОБАВЛЯЕМ!
        'purpose_id',
        'payer_company',
        'description',
        'order'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }

    // Автоматически заполняем project_id
    protected static function booted()
    {
        static::creating(function ($company) {
            if (!$company->project_id && $company->purpose_id) {
                $company->project_id = $company->purpose->project_id;
            }
        });

        static::updating(function ($company) {
            if (!$company->project_id && $company->purpose_id) {
                $company->project_id = $company->purpose->project_id;
            }
        });
    }
}
