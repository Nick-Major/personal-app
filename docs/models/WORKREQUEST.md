# 📋 WorkRequest - Модель заявки

## 🎯 Назначение
Основная сущность системы - заявка на выполнение работ.

## 📊 Основные поля
- `request_number` - номер заявки (автогенерация)
- `project_id` → Project - проект
- `purpose_id` → Purpose - назначение работ
- `category_id` → Category - категория исполнителей
- `personnel_type` - тип персонала: `our` | `contractor`
- `work_date` - дата работ
- `status` - статус заявки

## 🔄 Жизненный цикл
draft → published → in_progress → closed → completed
