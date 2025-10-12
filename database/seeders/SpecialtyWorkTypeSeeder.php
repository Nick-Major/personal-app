<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\WorkType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecialtyWorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем специальности (только если их нет)
        $specialties = [
            ['code' => 'admin', 'name' => 'Администраторы'],
            ['code' => 'decor', 'name' => 'Декораторы'],
            ['code' => 'gardener_helper', 'name' => 'Помощник садовника'],
            ['code' => 'gardener', 'name' => 'Садовники'],
            ['code' => 'gardener_chem', 'name' => 'Садовники (хим. обработка)'],
            ['code' => 'landscape', 'name' => 'Специалисты по озеленению'],
            ['code' => 'senior_admin', 'name' => 'Старшие администраторы'],
            ['code' => 'senior_decor', 'name' => 'Старшие декораторы'],
            ['code' => 'senior_gardener', 'name' => 'Старшие садовники'],
            ['code' => 'tree_installer', 'name' => 'Установщик деревьев'],
            ['code' => 'staff_specialist', 'name' => 'Штатные специалисты'],
        ];

        $createdSpecialties = 0;
        foreach ($specialties as $specialty) {
            if (!Specialty::where('code', $specialty['code'])->exists()) {
                Specialty::create($specialty);
                $createdSpecialties++;
            }
        }

        // Создаем виды работ (только если их нет)
        $workTypes = [
            'Уход',
            'Монтажные работы',
            'Высотные работы',
        ];

        $createdWorkTypes = 0;
        foreach ($workTypes as $workTypeName) {
            if (!WorkType::where('name', $workTypeName)->exists()) {
                WorkType::create(['name' => $workTypeName]);
                $createdWorkTypes++;
            }
        }

        $this->command->info('Specialties and Work Types seeding completed!');
        $this->command->info("Created specialties: {$createdSpecialties}");
        $this->command->info("Created work types: {$createdWorkTypes}");
        $this->command->info("Total specialties in DB: " . Specialty::count());
        $this->command->info("Total work types in DB: " . WorkType::count());
    }
}
