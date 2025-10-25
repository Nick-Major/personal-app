<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkRequestStatus extends Model
{
    protected $fillable = [
        'work_request_id', 'status', 'changed_at', 'changed_by_id', 'notes'
    ];

    protected $casts = [
        'changed_at' => 'datetime'
    ];

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
