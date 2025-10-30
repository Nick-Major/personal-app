# 📋 WorkRequest - Полная документация заявки

## 🎯 Назначение
Основная сущность системы - заявка на выполнение работ с полной проектной структурой.

## 📊 Структура модели

### Обязательные поля при создании:
```php
// Инициатор должен указать:
'initiator_id', 'workers_count', 'estimated_shift_duration', 'work_date'

// Остальные поля могут быть заполнены позже:
'personnel_type' // определяется диспетчером при комплектации
'brigadier_id'   // назначается через этап планирования
'category_id'    // определяется при назначении персонала
'contractor_id'  // определяется при выборе массового персонала
```

## Все поля модели:
```php
protected $fillable = [
    // Идентификация
    'request_number', 'status',
    
    // Участники процесса
    'initiator_id', 'brigadier_id', 'brigadier_manual', 'dispatcher_id', 'contractor_id',
    
    // Проектная структура
    'project_id', 'purpose_id', 'category_id', 'work_type_id',
    
    // Персонал (personnel_type определяется диспетчером позже)
    'personnel_type', 'workers_count', 'mass_personnel_names', 'total_worked_hours',
    
    // Время и адреса
    'work_date', 'start_time', 'estimated_shift_duration', // ОБЯЗАТЕЛЬНО при создании
    'address_id', 'custom_address', 'is_custom_address',
    
    // Дополнительно
    'additional_info',
    
    // Таймстампы процесса
    'published_at', 'staffed_at', 'completed_at'
];
```

## 🔄 Жизненный цикл и статусы

### Статусы заявки (расширенные для этапа планирования):

* draft → Черновик (создана инициатором)

* pending_brigadier_confirmation → Ожидает подтверждения бригадира ⭐ НОВЫЙ

* published → Опубликована (бригадир подтвержден)

* in_progress → Взята в работу (комплектуется)

* closed → Заявка закрыта (укомплектована)

* working → Выполнение работ

* completed → Заявка завершена

* cancelled → Заявка отменена

### Типы персонала (определяются диспетчером):

* personalized → Персонализированный (конкретные исполнители)

* mass → Массовый (обезличенный персонал подрядчика)

### Workflow с этапом планирования:

* Создание → draft (Инициатор создает заявку)

* Назначение бригадира → pending_brigadier_confirmation (Ожидает подтверждения)

* Подтверждение бригадира → published (Передача диспетчеру)

* Комплектование → in_progress (Диспетчер назначает исполнителей)

* Закрытие комплектации → closed (Заявка укомплектована)

* Выполнение работ → working (Исполнители работают)

* Завершение → completed (Все смены закрыты и подтверждены)

## 🎯 Бизнес-логика

### Генерация номера заявки:
```php
public function generateRequestNumber()
{
    if ($this->personnel_type === 'mass' && $this->contractor) {
        // Подрядчик: [код_подрядчика]-[ID]/[год]
        return $this->contractor->contractor_code . '-' . $this->id . '/' . now()->year;
    } else {
        // Наш персонал: [префикс_категории]-[ID]/[год]
        $prefix = $this->category?->prefix ?: 'WR';
        return $prefix . '-' . $this->id . '/' . now()->year;
    }
}
```

### Этап планирования - назначение бригадира:
```php
public function assignBrigadier($brigadierId, $workDate)
{
    // Проверка конфликтов
    $hasConflict = BrigadierAssignment::where('brigadier_id', $brigadierId)
        ->where('status', 'active')
        ->whereHas('assignment_dates', function ($query) use ($workDate) {
            $query->where('work_date', $workDate);
        })
        ->exists();

    if ($hasConflict) {
        throw new \Exception('Бригадир уже занят на эту дату');
    }

    $this->update([
        'brigadier_id' => $brigadierId,
        'status' => 'pending_brigadier_confirmation'
    ]);
}
```

## 🔗 Система связей

### Основные связи:
```php
public function initiator() { return $this->belongsTo(User::class, 'initiator_id'); }
public function brigadier() { return $this->belongsTo(User::class, 'brigadier_id'); }
public function dispatcher() { return $this->belongsTo(User::class, 'dispatcher_id'); }
public function category() { return $this->belongsTo(Category::class); }
public function workType() { return $this->belongsTo(WorkType::class); }
public function project() { return $this->belongsTo(Project::class); }
public function purpose() { return $this->belongsTo(Purpose::class); }
public function address() { return $this->belongsTo(Address::class); }
public function contractor() { return $this->belongsTo(Contractor::class); }
```

### Рабочие связи:
```php
public function shifts() { return $this->hasMany(Shift::class, 'request_id'); }
public function assignments() { return $this->hasMany(Assignment::class, 'request_id'); }
public function massPersonnelReports() { return $this->hasMany(MassPersonnelReport::class, 'request_id'); }
```

## 💡 Особенности реализации этапа планирования

### Назначение бригадира:

* Бригадиром может быть любой исполнитель (роль executor)

* Проверка на конфликты по датам через BrigadierAssignment

* Статус pending_brigadier_confirmation до подтверждения исполнителем

* При отказе исполнителя - система блокирует его на эту дату

### Определение типа персонала:

* Заявка может быть ТОЛЬКО на наших ИЛИ на подрядчика

* Персонализированный персонал - ОДНА категория

* Обезличенный персонал - ОДНА специальность

* Бригадир (наш/подрядчик/ручной) не влияет на тип персонала

### Валидация при создании:

* estimated_shift_duration - обязательно (инициатор знает длительность)

* personnel_type - nullable (определяется диспетчером позже)

* work_date - обязательно для планирования
