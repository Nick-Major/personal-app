<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contractor_code', // ← ДОБАВЛЕНО
        'contact_person',
        'contact_person_phone',
        'contact_person_email',
        'phone',
        'email',
        'user_id',
        'contract_type_id',
        'tax_status_id',
        'address',
        'inn',
        'bank_details',
        'specializations',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'specializations' => 'array',
        'is_active' => 'boolean',
    ];

    // === АВТОМАТИЧЕСКАЯ ГЕНЕРАЦИЯ КОДА ===
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contractor) {
            if (empty($contractor->contractor_code)) {
                $contractor->contractor_code = static::generateContractorCode($contractor->name);
            }
        });

        static::updating(function ($contractor) {
            // Обновляем код только если изменилось имя И код не меняли вручную
            if ($contractor->isDirty('name') && !$contractor->isDirty('contractor_code')) {
                $contractor->contractor_code = static::generateContractorCode($contractor->name);
            }
        });
    }

    public static function generateContractorCode($name)
    {
        // Берем первые буквы каждого слова (игнорируем ООО, ИП и т.д.)
        $ignoreWords = ['ооо', 'ип', 'зао', 'оао', 'llc', 'inc', 'ltd'];
        $words = array_filter(explode(' ', preg_replace('/[^a-zA-Zа-яА-Я0-9\s]/u', '', $name)));
        
        $code = '';
        foreach ($words as $word) {
            $cleanWord = trim($word);
            if (!empty($cleanWord) && !in_array(mb_strtolower($cleanWord), $ignoreWords)) {
                $code .= strtoupper(mb_substr($cleanWord, 0, 1));
                if (strlen($code) >= 3) break; // Максимум 3 буквы
            }
        }
        
        // Если код слишком короткий, берем первые буквы из названия
        if (strlen($code) < 2) {
            $cleaned = preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $name);
            $code = strtoupper(substr($cleaned, 0, 3));
        }
        
        // Проверяем уникальность
        $counter = 1;
        $originalCode = $code;
        
        while (static::where('contractor_code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
            if ($counter > 100) break;
        }
        
        return $code;
    }

    // === СУЩЕСТВУЮЩИЕ СВЯЗИ (без изменений) ===
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function executors()
    {
        return $this->hasMany(User::class, 'contractor_id')
                    ->whereHas('roles', function($q) {
                        $q->where('name', 'executor');
                    });
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class, 'contractor_id');
    }

    public function anonymousShifts()
    {
        return $this->hasMany(Shift::class)->whereNull('user_id');
    }

    public function allShifts()
    {
        return Shift::where('contractor_id', $this->id)
                   ->orWhereHas('user', function($q) {
                       $q->where('contractor_id', $this->id);
                   });
    }

    public function contractorRates()
    {
        return $this->hasMany(ContractorRate::class);
    }

    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
    }

    public function taxStatus()
    {
        return $this->belongsTo(TaxStatus::class);
    }

    // === СУЩЕСТВУЮЩИЕ МЕТОДЫ (без изменений) ===
    public function getTotalExecutorsCount()
    {
        return $this->executors()->count();
    }

    public function getActiveShiftsCount()
    {
        return $this->allShifts()->where('status', 'active')->count();
    }

    public function getCompletedShiftsThisMonth()
    {
        return $this->allShifts()
                   ->where('status', 'completed')
                   ->where('work_date', '>=', now()->startOfMonth())
                   ->count();
    }

    public function hasCategory($categoryId)
    {
        return $this->contractorRates()
            ->whereHas('specialty', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->where('is_active', true)
            ->exists();
    }
}
