<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateViewerUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-viewer-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает пользователя БД только для чтения';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Создаем пользователя
            DB::statement("CREATE USER IF NOT EXISTS 'viewer'@'%' IDENTIFIED BY 'viewer123'");
            
            // Даем права только на чтение
            DB::statement("GRANT SELECT ON laravel.* TO 'viewer'@'%'");
            
            // Применяем права
            DB::statement("FLUSH PRIVILEGES");
            
            $this->info('✅ Пользователь viewer создан успешно!');
            $this->info('👤 Логин: viewer');
            $this->info('🔑 Пароль: viewer123');
            $this->info('');
            $this->info('📋 Теперь создай новое подключение в DBeaver с этими данными:');
            $this->info('   Host: mysql');
            $this->info('   Database: laravel');
            $this->info('   Username: viewer');
            $this->info('   Password: viewer123');
            
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при создании пользователя: ' . $e->getMessage());
            $this->info('');
            $this->info('💡 Альтернатива: выполни SQL вручную в DBeaver:');
            $this->info("CREATE USER 'viewer'@'%' IDENTIFIED BY 'viewer123';");
            $this->info("GRANT SELECT ON laravel.* TO 'viewer'@'%';");
            $this->info("FLUSH PRIVILEGES;");
        }
    }
}
