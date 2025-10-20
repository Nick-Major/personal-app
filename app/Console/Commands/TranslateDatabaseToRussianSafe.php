<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateDatabaseToRussianSafe extends Command
{
    protected $signature = 'db:translate-russian-safe';
    protected $description = 'Безопасно добавляет русские комментарии к полям БД';

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
    ];

    // Поля, которые нельзя изменять (из-за foreign keys или reserved words)
    protected $skipFields = [
        'id', // Все ID поля (из-за foreign keys)
        'key', // Зарезервированное слово
        'order', // Зарезервированное слово
    ];

    // Таблицы, которые нужно пропустить (VIEWS и проблемные)
    protected $skipTables = [
        'v_%', // Все VIEWS
        'view_%', // Все VIEWS  
        'Адреса',
        'Даты назначений бригадиров',
        'Заявки на работы',
        'Назначения бригадиров',
        'Подрядчики',
        'Пользователи системы',
        'Проекты',
        'Смены',
        'Специальности',
        'Специальности пользователей',
        'Ставки оплаты',
    ];

    public function handle()
    {
        $this->info('🚀 Начинаем безопасный перевод полей БД...');
        
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        
        $totalTranslated = 0;
        $totalSkipped = 0;
        
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_'.$dbName};
            
            // Пропускаем VIEWS и проблемные таблицы
            if ($this->shouldSkipTable($tableName)) {
                $this->line("⏭️  Пропускаем таблицу: {$tableName}");
                $totalSkipped++;
                continue;
            }
            
            $this->info("📊 Обрабатываем таблицу: {$tableName}");
            
            try {
                $columns = DB::select("SHOW FULL COLUMNS FROM `{$tableName}`");
                
                foreach ($columns as $column) {
                    if ($this->shouldSkipField($column->Field)) {
                        $this->line("   ⏭️  Пропускаем поле: {$column->Field}");
                        continue;
                    }
                    
                    $russianComment = $this->translateColumn($column->Field);
                    
                    if ($russianComment) {
                        try {
                            // Экранируем зарезервированные слова
                            $fieldName = $this->escapeFieldName($column->Field);
                            
                            DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN {$fieldName} {$column->Type} COMMENT '{$russianComment}'");
                            $this->line("   ✅ {$column->Field} → {$russianComment}");
                            $totalTranslated++;
                        } catch (\Exception $e) {
                            $this->error("   ❌ Ошибка для {$column->Field}: {$e->getMessage()}");
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Ошибка таблицы {$tableName}: {$e->getMessage()}");
            }
        }
        
        $this->info("\n🎉 Готово!");
        $this->info("✅ Переведено полей: {$totalTranslated}");
        $this->info("⏭️  Пропущено таблиц: {$totalSkipped}");
        $this->info("💡 Обнови диаграмму в MySQL Workbench чтобы увидеть изменения!");
    }
    
    protected function shouldSkipTable($tableName)
    {
        foreach ($this->skipTables as $pattern) {
            if (str_contains($pattern, '%')) {
                if (str_starts_with($tableName, str_replace('%', '', $pattern))) {
                    return true;
                }
            } elseif ($tableName === $pattern) {
                return true;
            }
        }
        return false;
    }
    
    protected function shouldSkipField($fieldName)
    {
        return in_array($fieldName, $this->skipFields);
    }
    
    protected function translateColumn($columnName)
    {
        if (isset($this->dictionary[$columnName])) {
            return $this->dictionary[$columnName];
        }
        
        // Автоматический перевод snake_case в читаемый русский
        $translated = str_replace('_', ' ', $columnName);
        $translated = ucfirst($translated);
        
        return $translated;
    }
    
    protected function escapeFieldName($fieldName)
    {
        $reservedWords = ['key', 'order', 'group', 'table'];
        
        if (in_array(strtolower($fieldName), $reservedWords)) {
            return "`{$fieldName}`";
        }
        
        return $fieldName;
    }
}
