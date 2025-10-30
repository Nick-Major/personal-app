# 🎯 Система специальностей и ставок

## 🏗️ Архитектура

### Для нашего персонала:

User → UserSpecialty (pivot) → Specialty
↓
base_hourly_rate (в пивоте)

### Для подрядчиков:

Contractor → ContractorRate → Specialty
↓
hourly_rate (в ContractorRate)

### Объединение через Category:

Category → Specialty[] (специальности в категории)


## 📋 Правила системы

### ✅ Ограничения:
- **Один User** может иметь **несколько Specialty**
- **Одна Specialty** принадлежит **одной Category**  
- **В одной Category** может быть **много Specialty**
- **В заявке** выбирается **Category**, а не конкретная Specialty

### 💰 Расчет ставок:

#### Наш персонал:

// Берется из user_specialties.base_hourly_rate
$user->specialties()->where('id', $specialtyId)->first()->pivot->base_hourly_rate

#### Персонал подрядчика:

// Берется из contractor_rates.hourly_rate
ContractorRate::where('contractor_id', $contractorId)
             ->where('specialty_id', $specialtyId)
             ->where('is_active', true)
             ->first()->hourly_rate

#### Массовый персонал подрядчика:

// Та же логика, но без привязки к конкретному User
ContractorRate::where('contractor_id', $contractorId)
             ->where('specialty_id', $specialtyId) 
             ->where('is_anonymous', true)
             ->first()->hourly_rate

🎯 Логика в WorkRequest
При создании заявки выбирается Category, а система автоматически определяет:

Какие Specialty доступны в этой Category

Какие User/Contractor имеют эти Specialty

Какие ставки применять