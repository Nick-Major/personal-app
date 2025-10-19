<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddDatabaseComments extends Command
{
    protected $signature = 'db:add-comments';
    protected $description = 'Add Russian comments to database tables and columns';

    public function handle()
    {
        $comments = [
            'users' => [
                'table' => 'Пользователи системы',
                'columns' => [
                    'name' => 'Имя пользователя',
                    'email' => 'Электронная почта',
                    'email_verified_at' => 'Дата подтверждения email',
                    'password' => 'Хэш пароля',
                    'remember_token' => 'Токен для запоминания',
                ]
            ],
            'projects' => [
                'table' => 'Проекты и задания',
                'columns' => [
                    'name' => 'Название проекта',
                    'description' => 'Описание проекта',
                ]
            ],
            'addresses' => [
                'table' => 'Адреса объектов',
                'columns' => [
                    'name' => 'Название адреса',
                    'full_address' => 'Полный адрес',
                    'description' => 'Описание адреса',
                ]
            ],
            'work_requests' => [
                'table' => 'Заявки на выполнение работ',
                'columns' => [
                    'request_number' => 'Номер заявки',
                    'description' => 'Описание работы',
                ]
            ],
            'shifts' => [
                'table' => 'Рабочие смены',
                'columns' => [
                    'shift_number' => 'Номер смены',
                    'start_time' => 'Время начала',
                    'end_time' => 'Время окончания',
                ]
            ],
            // Добавьте остальные таблицы по аналогии
        ];

        foreach ($comments as $tableName => $tableData) {
            // Добавляем комментарий к таблице
            try {
                DB::statement("ALTER TABLE `{$tableName}` COMMENT = '{$tableData['table']}'");
                $this->info("Added comment to table: {$tableName}");
            } catch (\Exception $e) {
                $this->warn("Could not add comment to table {$tableName}: " . $e->getMessage());
            }

            // Добавляем комментарии к колонкам
            foreach ($tableData['columns'] as $columnName => $comment) {
                try {
                    $columnInfo = DB::selectOne("SHOW COLUMNS FROM `{$tableName}` WHERE Field = ?", [$columnName]);
                    if ($columnInfo) {
                        $type = $columnInfo->Type;
                        $null = $columnInfo->Null === 'YES' ? 'NULL' : 'NOT NULL';
                        $default = $columnInfo->Default ? "DEFAULT '{$columnInfo->Default}'" : '';
                        
                        DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$columnName}` {$type} {$null} {$default} COMMENT '{$comment}'");
                        $this->info("Added comment to column: {$tableName}.{$columnName}");
                    }
                } catch (\Exception $e) {
                    $this->warn("Could not add comment to column {$tableName}.{$columnName}: " . $e->getMessage());
                }
            }
        }

        $this->info('Database comments added successfully!');
    }
}
