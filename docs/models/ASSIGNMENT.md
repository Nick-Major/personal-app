# 📋 Документация: Сущность Assignment

## 🎯 Общее описание

**Assignment** - единая система назначений, объединяющая функциональность плановых назначений бригадиров и назначений исполнителей на заявки.

## 📊 Типы назначений

### 1. 🔧 `brigadier_schedule` - Плановые назначения бригадиров
**Назначение:** Плановый выход бригадиров в смену по графику
**Особенности:**
- Создается инициатором заранее
- Требует подтверждения от бригадира
- Автоматически создает смену при подтверждении
- Имеет уникальный номер назначения

**Поля:**
- `work_request_id` = NULL
- `assignment_number` = заполнен (автогенерация)
- `shift_id` = заполняется автоматически

### 2. 📋 `work_request` - Назначения на заявки  
**Назначение:** Назначение исполнителей на конкретные заявки
**Особенности:**
- Создается диспетчером при комплектовании заявки
- Не требует подтверждения
- Смена создается вручную/через API

**Поля:**
- `work_request_id` = заполнен
- `assignment_number` = NULL
- `shift_id` = NULL (смена создается отдельно)

### 3. 👥 `mass_personnel` - Массовый персонал
**Назначение:** Назначения для массового (неперсонализированного) персонала
**Особенности:** (зарезервировано для будущего развития)

## 🗂️ Структура данных

### Основные поля:
```php
// Идентификация
'id'                    // Уникальный идентификатор
'assignment_type'       // Тип назначения: brigadier_schedule, work_request, mass_personnel
'user_id'              // Ссылка на пользователя (исполнитель/бригадир)
'work_request_id'      // Ссылка на заявку (NULL для бригадиров)

// Планирование
'planned_date'         // Планируемая дата работы
'planned_start_time'   // Время начала работы
'planned_duration_hours' // Продолжительность смены (часов)

// Адрес работы
'planned_address_id'   // Официальный адрес
'planned_custom_address' // Неофициальный адрес  
'is_custom_planned_address' // Флаг использования неофициального адреса

// Статус и подтверждение
'status'              // pending, confirmed, rejected, completed
'confirmed_at'        // Дата подтверждения
'rejected_at'         // Дата отклонения
'rejection_reason'    // Причина отказа

// Связи
'shift_id'            // Созданная смена (для бригадиров)
'assignment_number'   // Уникальный номер назначения (для бригадиров)

// Дополнительно
'role_in_shift'       // executor, brigadier
'source'              // dispatcher, initiator
'assignment_comment'  // Комментарий к назначению
```

## 🔄 Workflow

### Для бригадиров (brigadier_schedule):

[Инициатор создает назначение] 
    → [Статус: pending]
    → [Бригадир подтверждает] 
    → [Статус: confirmed] 
    → [Автоматически создается смена]
    → [Shift создана со assignment_number]

### Для исполнителей (work_request):

[Диспетчер создает назначение]
    → [Статус: confirmed]
    → [Исполнитель выходит в смену через API]
    → [Shift создается вручную с request_id]

## 🛠️ API Methods

### Основные endpoints:

// CRUD операции
GET    /api/assignments           // Список назначений
POST   /api/assignments           // Создание назначения
GET    /api/assignments/{id}      // Получение назначения
PUT    /api/assignments/{id}      // Обновление назначения
DELETE /api/assignments/{id}      // Удаление назначения

// Бизнес-логика
POST   /api/assignments/{id}/confirm  // Подтверждение назначения
POST   /api/assignments/{id}/reject   // Отклонение назначения
GET    /api/my/assignments           // Мои назначения

### Key Methods в модели:
```
// Статусные методы
$assignment->confirm()           // Подтвердить назначение
$assignment->reject($reason)    // Отклонить с причиной
$assignment->complete()          // Завершить назначение

// Проверки статуса
$assignment->isPending()         // Ожидает подтверждения
$assignment->isConfirmed()       // Подтверждено
$assignment->isBrigadierSchedule() // Является плановым назначением
$assignment->isWorkRequest()     // Является назначением на заявку

// Вспомогательные
$assignment->planned_end_time    // Расчетное время окончания
$assignment->full_planned_address // Полный адрес работы
```

### 🎯 Scopes для фильтрации

```
// По типу назначения
Assignment::brigadierSchedules()  // Только плановые назначения
Assignment::workRequests()        // Только назначения на заявки

// По статусу
Assignment::pending()             // Ожидающие подтверждения
Assignment::confirmed()           // Подтвержденные
Assignment::rejected()            // Отклоненные

// Специальные
Assignment::active()              // Активные (pending + confirmed)
Assignment::brigadierAssignments() // Бригадиры (устаревший scope)
Assignment::executorAssignments()  // Исполнители (устаревший scope)
```

## 🔍 Observer Logic

### AssignmentObserver автоматизирует:

1. Генерация номера назначения при создании бригадирского назначения

2. Создание смены при подтверждении бригадирского назначения

3. Обновление временных меток при изменении статуса

## 💡 Best Practices

### При создании назначений:
```
// Бригадирское назначение
Assignment::create([
    'assignment_type' => 'brigadier_schedule',
    'user_id' => $brigadierId,
    'role_in_shift' => 'brigadier',
    // work_request_id должен быть NULL
]);

// Назначение на заявку  
Assignment::create([
    'assignment_type' => 'work_request', 
    'user_id' => $executorId,
    'work_request_id' => $requestId,
    'role_in_shift' => 'executor',
]);
```

### При работе со статусами:
```
// Правильно - использовать методы модели
$assignment->confirm();
$assignment->reject('Причина отказа');

// Неправильно - прямое обновление
$assignment->update(['status' => 'confirmed']);
```
## 🚨 Важные особенности

1. Валидация: Для work_request обязателен work_request_id, для brigadier_schedule он должен быть NULL

2. Автоматизация: Смена создается ТОЛЬКО для подтвержденных brigadier_schedule назначений

3. Нумерация: Номера назначений генерируются автоматически только для бригадиров

4. Статусы: Действия "Подтвердить"/"Отклонить" доступны только для статуса pending