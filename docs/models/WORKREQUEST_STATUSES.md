🔄 WorkRequest - Жизненный цикл статусов

📊 Статусы заявки

const STATUS_PUBLISHED = 'published';      // Опубликована
const STATUS_IN_PROGRESS = 'in_progress';  // Взята в работу
const STATUS_CLOSED = 'closed';           // Заявка закрыта
const STATUS_NO_SHIFTS = 'no_shifts';     // Смены не открыты
const STATUS_WORKING = 'working';         // Выполнение работ
const STATUS_UNCLOSED = 'unclosed';       // Смены не закрыты
const STATUS_COMPLETED = 'completed';     // Заявка завершена
const STATUS_CANCELLED = 'cancelled';     // Заявка отменена

🎯 Типы персонала

const PERSONNEL_OUR = 'our';           // Наш персонал
const PERSONNEL_CONTRACTOR = 'contractor'; // Подрядчик

📈 Типичный workflow

1. Создание → draft

2. Публикация → published

3. Комплектование → in_progress

4. Закрытие → closed

5. Выполнение → working → completed