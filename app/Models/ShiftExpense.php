<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftExpense extends Model
{
    use HasFactory;

    const TYPE_TAXI = 'taxi';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'shift_id',
        'type', 
        'amount',
        'receipt_photo', // ПЕРЕХОДИМ НА receipt_photo
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
