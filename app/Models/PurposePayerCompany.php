<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposePayerCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'purpose_id',
        'payer_company', // ДОБАВЛЯЕМ!
        'description',
        'order'
    ];

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }
}
