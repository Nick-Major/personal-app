<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_address', 
        'location_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
