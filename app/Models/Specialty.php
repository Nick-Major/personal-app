<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix', // ← ДОБАВЛЕНО
        'description',
        'category_id',
        'base_hourly_rate',
        'is_active'
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // === АВТОМАТИЧЕСКАЯ ГЕНЕРАЦИЯ ПРЕФИКСА ===
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($specialty) {
            if (empty($specialty->prefix)) {
                $specialty->prefix = static::generatePrefix($specialty->name);
            }
        });

        static::updating(function ($specialty) {
            // Обновляем префикс только если изменилось имя И префикс не меняли вручную
            if ($specialty->isDirty('name') && !$specialty->isDirty('prefix')) {
                $specialty->prefix = static::generatePrefix($specialty->name);
            }
        });
    }

    public static function generatePrefix($name)
    {
        // Убираем все не-буквы и берем первые 3-4 символа
        $cleaned = preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $name);
        $prefix = strtoupper(substr($cleaned, 0, 4));
        
        // Если получилось меньше 2 символов, используем транслитерацию
        if (strlen($prefix) < 2) {
            $transliterated = transliterator_transliterate('Russian-Latin/BGN', $name);
            $cleaned = preg_replace('/[^a-zA-Z]/', '', $transliterated);
            $prefix = strtoupper(substr($cleaned, 0, 4));
        }
        
        // Проверяем уникальность
        $counter = 1;
        $originalPrefix = $prefix;
        
        while (static::where('prefix', $prefix)->exists()) {
            $prefix = $originalPrefix . $counter;
            $counter++;
            if ($counter > 100) break;
        }
        
        return $prefix;
    }

    // === СУЩЕСТВУЮЩИЕ СВЯЗИ (без изменений) ===
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialties')
                    ->withPivot('base_hourly_rate', 'assigned_at', 'assigned_by')
                    ->withTimestamps();
    }

    public function contractorRates()
    {
        return $this->hasMany(ContractorRate::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
