<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'file_path',
        'original_name',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}


