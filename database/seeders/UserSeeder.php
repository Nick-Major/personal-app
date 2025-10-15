<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем роли если их нет
        $roles = ['admin', 'initiator', 'dispatcher', 'executor', 'brigadier'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Создаем администратора
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'phone' => '+79999999999',
            ]
        );
        
        $admin->assignRole('admin');

        // Создаем инициатора
        $initiator = User::firstOrCreate(
            ['email' => 'initiator@example.com'],
            [
                'name' => 'Инициатор Тестовый',
                'password' => Hash::make('password'),
                'phone' => '+79999999998',
            ]
        );
        
        $initiator->assignRole('initiator');

        // Создаем диспетчера
        $dispatcher = User::firstOrCreate(
            ['email' => 'dispatcher@example.com'],
            [
                'name' => 'Диспетчер Тестовый',
                'password' => Hash::make('password'),
                'phone' => '+79999999997',
            ]
        );
        
        $dispatcher->assignRole('dispatcher');

        // Создаем исполнителя
        $executor = User::firstOrCreate(
            ['email' => 'executor@example.com'],
            [
                'name' => 'Исполнитель Тестовый',
                'password' => Hash::make('password'),
                'phone' => '+79999999996',
            ]
        );
        
        $executor->assignRole('executor');

        // Создаем бригадира
        $brigadier = User::firstOrCreate(
            ['email' => 'brigadier@example.com'],
            [
                'name' => 'Бригадир Тестовый',
                'password' => Hash::make('password'),
                'phone' => '+79999999995',
            ]
        );
        
        $brigadier->assignRole('brigadier');

        $this->command->info('Тестовые пользователи созданы!');
        $this->command->info('Админ: admin@example.com / password');
        $this->command->info('Инициатор: initiator@example.com / password');
        $this->command->info('Диспетчер: dispatcher@example.com / password');
        $this->command->info('Исполнитель: executor@example.com / password');
        $this->command->info('Бригадир: brigadier@example.com / password');
    }
}
