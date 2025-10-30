# 📋 WorkRequest - Полная документация заявки

## 🎯 Назначение
Основная сущность системы - заявка на выполнение работ с полной проектной структурой.

## 📊 Структура модели

### Все поля модели:
```php
protected $fillable = [
    // Идентификация
    'request_number', 'status',
    
    // Участники процесса
    'initiator_id', 'brigadier_id', 'brigadier_manual', 'dispatcher_id', 'contractor_id',
    
    // Проектная структура
    'project_id', 'purpose_id', 'category_id', 'work_type_id',
    
    // Персонал
    'personnel_type', 'workers_count', 'mass_personnel_names', 'total_worked_hours',
    
    // Время и адреса
    'work_date', 'start_time', 'estimated_shift_duration',
    'address_id', 'custom_address', 'is_custom_address',
    
    // Дополнительно
    'additional_info',
    
    // Таймстампы процесса
    'published_at', 'staffed_at', 'completed_at'
];
```

## 🔄 Жизненный цикл и статусы

### Статусы заявки:

* draft → Черновик

* published → Опубликована

* in_progress → Взята в работу (комплектуется)

* closed → Заявка закрыта (укомплектована)

* working → Выполнение работ

* completed → Заявка завершена

* cancelled → Заявка отменена

### Типы персонала:

* personalized → Персонализированный (конкретные исполнители)

* mass → Массовый (обезличенный персонал подрядчика)

### Типичный workflow:

1. Создание → draft (Инициатор)

2. Публикация → published (передача диспетчеру)

3. Комплектование → in_progress (Диспетчер назначает исполнителей)

4. Закрытие комплектации → closed (заявка укомплектована)

5. Выполнение работ → working (исполнители работают)

6. Завершение → completed (все смены закрыты и подтверждены)

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

### Определение типа исполнителя:
```php
public function getExecutorTypeAttribute()
{
    if ($this->assignments()->whereNotNull('user_id')->exists()) {
        return 'personalized'; // Персонализированные исполнители
    }
    if ($this->mass_personnel_names) {
        return 'mass'; // Массовый персонал
    }
    return null;
}
```

### Контактное лицо на объекте:
```php
public function getContactPersonAttribute()
{
    return $this->brigadier_manual ?: $this->brigadier?->full_name;
}
```

### Финальный адрес работ:
```php
public function getFinalAddressAttribute()
{
    return $this->is_custom_address ? $this->custom_address : $this->address?->full_address;
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

## 📈 Метрики и отчетность

### Статистика по заявке:

* Количество назначенных исполнителей

* Общее отработанное время (total_worked_hours)

* Статус выполнения работ

* Финансовые показатели (через связанные смены)

## Workflow управления:

1. Создание - Инициатор создает заявку с проектом/целью

2. Публикация - Заявка передается диспетчеру (published_at)

3. Комплектование - Назначение исполнителей/подрядчика (staffed_at)

4. Выполнение - Исполнители работают по сменам

5. Завершение - Все смены закрыты, расчеты выполнены (completed_at)

## 💡 Особенности реализации

### Для массового персонала:

* Заполняется поле mass_personnel_names с ФИО исполнителей

* Расчеты через MassPersonnelReport

* Управление бригадиром за всю бригаду

### Для персонализированного персонала:

* Назначение конкретных исполнителей через assignments

* Индивидуальные смены через shifts

* Подробная детализация по каждому исполнителю

### Адресная система:

* Использование существующих адресов (address_id)

* Возможность указания кастомного адреса (custom_address)

* Автоматическое определение финального адреса
