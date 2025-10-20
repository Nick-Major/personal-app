<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Основные таблицы пользователей и ролей
        DB::statement("ALTER TABLE users COMMENT = 'Пользователи системы'");
        DB::statement("ALTER TABLE roles COMMENT = 'Роли пользователей'");
        DB::statement("ALTER TABLE permissions COMMENT = 'Разрешения системы'");
        DB::statement("ALTER TABLE model_has_roles COMMENT = 'Связь моделей с ролями'");
        DB::statement("ALTER TABLE model_has_permissions COMMENT = 'Связь моделей с разрешениями'");
        DB::statement("ALTER TABLE role_has_permissions COMMENT = 'Связь ролей с разрешениями'");
        
        // Таблицы основной бизнес-логики
        DB::statement("ALTER TABLE work_requests COMMENT = 'Заявки на работы'");
        DB::statement("ALTER TABLE brigadier_assignments COMMENT = 'Назначения бригадиров'");
        DB::statement("ALTER TABLE brigadier_assignment_dates COMMENT = 'Даты назначений бригадиров'");
        DB::statement("ALTER TABLE initiator_grants COMMENT = 'Права инициаторов'");
        
        // Персонал и специальности
        DB::statement("ALTER TABLE specialties COMMENT = 'Специальности'");
        DB::statement("ALTER TABLE user_specialties COMMENT = 'Специальности пользователей'");
        DB::statement("ALTER TABLE assignments COMMENT = 'Назначения исполнителей'");
        DB::statement("ALTER TABLE contractors COMMENT = 'Подрядчики'");
        
        // Работа и смены
        DB::statement("ALTER TABLE shifts COMMENT = 'Смены'");
        DB::statement("ALTER TABLE shift_segments COMMENT = 'Сегменты смен'");
        DB::statement("ALTER TABLE shift_photos COMMENT = 'Фотографии смен'");
        
        // Проекты и адреса
        DB::statement("ALTER TABLE projects COMMENT = 'Проекты'");
        DB::statement("ALTER TABLE addresses COMMENT = 'Адреса'");
        DB::statement("ALTER TABLE address_project COMMENT = 'Связь адресов с проектами'");
        DB::statement("ALTER TABLE project_assignments COMMENT = 'Назначения проектов'");
        
        // Финансы и ставки
        DB::statement("ALTER TABLE rates COMMENT = 'Ставки оплаты'");
        DB::statement("ALTER TABLE expenses COMMENT = 'Расходы'");
        DB::statement("ALTER TABLE receipts COMMENT = 'Чеки'");
        
        // Дополнительные таблицы
        DB::statement("ALTER TABLE purposes COMMENT = 'Назначения работ'");
        DB::statement("ALTER TABLE purpose_templates COMMENT = 'Шаблоны назначений'");
        DB::statement("ALTER TABLE purpose_payer_companies COMMENT = 'Компании-плательщики'");
        DB::statement("ALTER TABLE purpose_address_rules COMMENT = 'Правила адресов назначений'");
        DB::statement("ALTER TABLE work_types COMMENT = 'Типы работ'");
        DB::statement("ALTER TABLE visited_locations COMMENT = 'Посещенные локации'");
        
        // Системные таблицы Laravel
        DB::statement("ALTER TABLE migrations COMMENT = 'Миграции базы данных'");
        DB::statement("ALTER TABLE password_reset_tokens COMMENT = 'Токены сброса пароля'");
        DB::statement("ALTER TABLE failed_jobs COMMENT = 'Неудачные задания очереди'");
        DB::statement("ALTER TABLE personal_access_tokens COMMENT = 'Токены персонального доступа'");
        DB::statement("ALTER TABLE sessions COMMENT = 'Сессии пользователей'");
        DB::statement("ALTER TABLE cache COMMENT = 'Кэш системы'");
        DB::statement("ALTER TABLE cache_locks COMMENT = 'Блокировки кэша'");
        DB::statement("ALTER TABLE job_batches COMMENT = 'Пакеты заданий'");
        DB::statement("ALTER TABLE jobs COMMENT = 'Очередь заданий'");
    }

    public function down()
    {
        // Удаляем все комментарии при откате миграции
        $tables = [
            'users', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions',
            'work_requests', 'brigadier_assignments', 'brigadier_assignment_dates', 'initiator_grants',
            'specialties', 'user_specialties', 'assignments', 'contractors',
            'shifts', 'shift_segments', 'shift_photos',
            'projects', 'addresses', 'address_project', 'project_assignments',
            'rates', 'expenses', 'receipts',
            'purposes', 'purpose_templates', 'purpose_payer_companies', 'purpose_address_rules', 
            'work_types', 'visited_locations',
            'migrations', 'password_reset_tokens', 'failed_jobs', 'personal_access_tokens',
            'sessions', 'cache', 'cache_locks', 'job_batches', 'jobs'
        ];
        
        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} COMMENT = ''");
        }
    }
};
