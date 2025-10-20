<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateDatabaseToRussian extends Command
{
    protected $signature = 'db:translate-russian';
    protected $description = 'Автоматически добавляет русские комментарии ко всем полям БД';

    // Словарь перевода для common полей
    protected $dictionary = [
        // Основные поля
        'id' => 'ID',
        'name' => 'Название',
        'title' => 'Заголовок',
        'description' => 'Описание',
        'comment' => 'Комментарий',
        'notes' => 'Заметки',
        
        // Пользователи
        'email' => 'Email',
        'password' => 'Пароль',
        'remember_token' => 'Токен запоминания',
        'email_verified_at' => 'Дата подтверждения email',
        
        // Даты
        'created_at' => 'Дата создания',
        'updated_at' => 'Дата обновления',
        'deleted_at' => 'Дата удаления',
        'published_at' => 'Дата публикации',
        'completed_at' => 'Дата завершения',
        'start_time' => 'Время начала',
        'end_time' => 'Время окончания',
        'work_date' => 'Дата работы',
        
        // Статусы
        'status' => 'Статус',
        'state' => 'Состояние',
        'type' => 'Тип',
        
        // Работа и персонал
        'workers_count' => 'Количество рабочих',
        'shift_duration' => 'Продолжительность смены (часы)',
        'executor_type' => 'Тип исполнителя',
        'dispatcher_id' => 'ID диспетчера',
        'initiator_id' => 'ID инициатора',
        'brigadier_id' => 'ID бригадира',
        'user_id' => 'ID пользователя',
        
        // Проекты и адреса
        'project_id' => 'ID проекта',
        'purpose_id' => 'ID назначения',
        'address_id' => 'ID адреса',
        'specialty_id' => 'ID специальности',
        'work_type_id' => 'ID типа работ',
        
        // Финансы
        'price' => 'Цена',
        'amount' => 'Сумма',
        'rate' => 'Ставка',
        'cost' => 'Стоимость',
        'total' => 'Итого',
        
        // Контакты
        'phone' => 'Телефон',
        'address' => 'Адрес',
        'location' => 'Местоположение',
        'coordinates' => 'Координаты',
        
        // Файлы
        'image' => 'Изображение',
        'photo' => 'Фотография',
        'file' => 'Файл',
        'document' => 'Документ',
    ];

    public function handle()
    {
        $this->info('🚀 Начинаем автоматический перевод полей БД...');
        
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        
        $totalTranslated = 0;
        
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_'.$dbName};
            $this->info("\n📊 Таблица: {$tableName}");
            
            $columns = DB::select("SHOW FULL COLUMNS FROM {$tableName}");
            
            foreach ($columns as $column) {
                $russianComment = $this->translateColumn($column->Field);
                
                if ($russianComment) {
                    try {
                        // Добавляем комментарий к полю
                        DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN {$column->Field} {$column->Type} COMMENT '{$russianComment}'");
                        $this->line("   ✅ {$column->Field} → {$russianComment}");
                        $totalTranslated++;
                    } catch (\Exception $e) {
                        $this->error("   ❌ Ошибка для {$column->Field}: {$e->getMessage()}");
                    }
                }
            }
        }
        
        $this->info("\n🎉 Готово! Переведено полей: {$totalTranslated}");
        $this->info("💡 Обнови диаграмму в MySQL Workbench чтобы увидеть изменения!");
    }
    
    protected function translateColumn($columnName)
    {
        // Пропускаем уже переведенные поля
        if (isset($this->dictionary[$columnName])) {
            return $this->dictionary[$columnName];
        }
        
        // Автоматический перевод snake_case в читаемый русский
        $translated = str_replace('_', ' ', $columnName);
        $translated = ucfirst($translated);
        
        return $translated;
    }
}
