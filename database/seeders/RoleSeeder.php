<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем роли
        $roles = [
            'initiator',    // Инициатор заявки
            'executor',     // Исполнитель
            'brigadier',    // Бригадир
            'dispatcher',   // Диспетчер
            'contractor',   // Подрядчик
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Создаем разрешения (при необходимости)
        $permissions = [
            'create_requests',
            'manage_requests', 
            'assign_brigadiers',
            'manage_personnel',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
