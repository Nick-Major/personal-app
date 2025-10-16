<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurposeAddressRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'purpose_id',
        'address_id',
        'payer_company', // ДОБАВЛЯЕМ!
        'priority'
    ];

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
