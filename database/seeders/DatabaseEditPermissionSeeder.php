<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DatabaseEditPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем специальное разрешение для редактирования БД
        $dbEditPermission = Permission::firstOrCreate([
            'name' => 'edit_database'
        ], [
            // УБИРАЕМ description - его нет в таблице
            'guard_name' => 'web'
        ]);

        $this->command->info('✅ Разрешение "edit_database" создано');
        $this->command->info('💡 Теперь админ может выборочно давать это право пользователям с ролями Инициатор и Диспетчер');
    }
}
