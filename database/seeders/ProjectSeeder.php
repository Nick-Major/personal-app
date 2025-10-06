<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Проекты и назначения из вашей таблицы
        $projectsData = [
            'Цветочный Джем25' => [
                'Застройка' => 'ЦЕХ/5С',
                'Монтаж, демонтаж' => 'ЦФ',
                'Ботанический сад/Монтаж, демонтаж' => 'ЦФ', 
                'Магистральная/Монтаж, демонтаж' => 'ЦФ',
                'Орг расходы' => 'ЦФ',
                'Уход' => 'УС',
                'Техзона/Уход' => 'УС',
                'Магистральная/Уход' => 'ЦФ',
            ],
        ];

        // Виды работ из ТЗ
        $workTypes = [
            'высотные работы',
            'демонтажные работы', 
            'другое',
            'монтажные работы',
            'обработка удобрениями',
            'погрузочно-разгрузочные работы',
            'полив растений', 
            'посадка растений',
            'работы по уходу за растениями',
            'разгрузка деревьев',
            'установка деревьев',
            'установка заборов'
        ];

        // Сохраняем виды работ в базу (можно создать отдельную таблицу)
        foreach ($workTypes as $workType) {
            DB::table('work_types')->insert(['name' => $workType]);
        }

        // Создаем таблицу для проектов и назначений если нужно
        foreach ($projectsData as $projectName => $assignments) {
            foreach ($assignments as $assignmentName => $payerCompany) {
                DB::table('project_assignments')->insert([
                    'project_name' => $projectName,
                    'assignment_name' => $assignmentName, 
                    'payer_company' => $payerCompany,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
