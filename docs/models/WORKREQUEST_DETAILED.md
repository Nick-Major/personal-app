# 📋 WorkRequest - Полная документация

## 🏗️ Структура модели
```bash
'request_number', 'project_id', 'purpose_id', 'address_id', 'initiator_id', 
'brigadier_id', 'brigadier_manual', 'category_id', 'work_type_id', 'contractor_id',
'personnel_type', 'mass_personnel_names', 'total_worked_hours', 'workers_count',
'estimated_shift_duration', 'work_date', 'start_time', 'custom_address', 
'is_custom_address', 'additional_info', 'status', 'dispatcher_id', 'published_at',
'staffed_at', 'completed_at', 'created_at', 'updated_at'

🎯 Бизнес-логика

Генерация номера заявки:

// Наш персонал: [префикс_категории]-[ID]/[год]  
// Подрядчик: [код_подрядчика]-[ID]/[год]
public function generateRequestNumber()

Определение типа исполнителя:

public function getExecutorTypeAttribute()
{
    if ($this->assignments()->whereNotNull('user_id')->exists()) {
        return 'personalized';
    }
    if ($this->mass_personnel_names) {
        return 'mass';
    }
    return null;
}

🔗 Связи

initiator() → User

brigadier() → User

category() → Category

contractor() → Contractor

shifts() → Shift[]

assignments() → Assignment[]
