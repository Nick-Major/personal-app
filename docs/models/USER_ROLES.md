# Актуализирую USER_ROLES.md
cat > docs/models/USER_ROLES.md << 'EOF'
# 👥 User - Актуальная ролевая модель

## 🎭 Система ролей и состояний

### Базовые роли (Spatie Permissions):
- **admin** - Полный доступ ко всей системе
- **initiator** - Создание заявок, назначение бригадиров
- **dispatcher** - Комплектование заявок, управление сменами
- **executor** - Базовые права исполнителя

### Состояния исполнителя:
- **👷 Executor** (базовое) - Выход на смены, открытие/закрытие смен
- **🎯 Brigadier** - Назначается на дату, управляет командой на объекте
- **👑 Brigadier-Initiator** - Исполнитель с правами создания заявок (только на себя)

### Специальные сущности:
- **🏢 Contractor** - Внешние подрядчики (отдельная модель)
- **👤 Contractor User** - Персонализированный персонал подрядчика

## 📊 Структура пользователя

### Основные поля:
```php
// Личные данные
'name', 'surname', 'patronymic', 'phone', 'telegram_id'

// Профессиональные
'contractor_id', // Связь с подрядчиком (для персонализированного персонала)
'contract_type_id', // Тип контракта
'tax_status_id', // Налоговый статус

// Специальности
'specialties' // BelongsToMany через user_specialties
```

### Accessors (вычисляемые поля):

* executor_type - Тип исполнителя (наш/подрядчик)

* user_type - Общий тип пользователя

* full_name - Полное ФИО

## 🔗 Ключевые связи

### Специальности и ставки:

```php
// Многие-ко-многим со ставками
public function specialties()
{
    return $this->belongsToMany(Specialty::class, 'user_specialties')
        ->withPivot('base_hourly_rate');
}
```

### Назначения и заявки:
```php
// Как инициатор
public function initiatedRequests() { return $this->hasMany(WorkRequest::class, 'initiator_id'); }

// Как бригадир  
public function brigadierRequests() { return $this->hasMany(WorkRequest::class, 'brigadier_id'); }

// Как диспетчер
public function dispatcherRequests() { return $this->hasMany(WorkRequest::class, 'dispatcher_id'); }

// Бригадирские назначения
public function brigadierAssignments() { return $this->hasMany(BrigadierAssignment::class, 'user_id'); }
```

## 🎯 Бригадир-Инициатор система

### Делегирование прав:
```php
// InitiatorGrant модель управляет правами
public function grantedInitiatorRights() { return $this->hasMany(InitiatorGrant::class, 'granted_to_id'); }
public function givenInitiatorRights() { return $this->hasMany(InitiatorGrant::class, 'granted_by_id'); }
```
### Ограничения Бригадир-Инициатора:

* Может создавать заявки только на себя

* Только на даты своего назначения бригадиром

* Ограниченный доступ к функциям инициатора
