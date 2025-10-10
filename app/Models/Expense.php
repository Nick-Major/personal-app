<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'type',
        'amount',
        'minutes',
        'comment',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }
}


