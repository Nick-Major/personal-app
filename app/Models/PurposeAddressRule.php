<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposeAddressRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', // ДОБАВЛЯЕМ!
        'purpose_id',
        'address_id', 
        'payer_company',
        'priority'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Автоматически заполняем project_id при создании
    protected static function booted()
    {
        static::creating(function ($rule) {
            if (!$rule->project_id && $rule->purpose_id) {
                $rule->project_id = $rule->purpose->project_id;
            }
        });

        static::updating(function ($rule) {
            if (!$rule->project_id && $rule->purpose_id) {
                $rule->project_id = $rule->purpose->project_id;
            }
        });
    }
}
