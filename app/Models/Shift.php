<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'contractor_id',
        'contractor_worker_name',
        'work_date',
        'start_time',
        'end_time',
        'status',
        'shift_started_at',
        'shift_ended_at',
        'notes',
        'worked_minutes',
        'lunch_minutes',
        'travel_expense_amount',
        'specialty_id',
        'work_type_id',
        'hourly_rate_snapshot',
        'total_amount',
        'expenses_total',
        'grand_total',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'shift_started_at' => 'datetime',
        'shift_ended_at' => 'datetime',
    ];

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function segments()
    {
        return $this->hasMany(ShiftSegment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'contractor_id',
        'contractor_worker_name',
        'work_date',
        'start_time',
        'end_time',
        'status',
        'shift_started_at',
        'shift_ended_at',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'shift_started_at' => 'datetime',
        'shift_ended_at' => 'datetime',
    ];

    // Связи
    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}
