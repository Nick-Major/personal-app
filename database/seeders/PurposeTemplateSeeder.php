<?php

namespace Database\Seeders;

use App\Models\PurposeTemplate;
use Illuminate\Database\Seeder;

class PurposeTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Застройка',
                'description' => 'Конструктив, МАФ, оборудование, инструменты, утилизация'
            ],
            [
                'name' => 'Монтаж, демонтаж', 
                'description' => 'Инвентарь, расходные материалы, сыпучие материалы, работы по монтажу/демонтажу'
            ],
            [
                'name' => 'Уход',
                'description' => 'Расходы по уходу за растениями, фонд замен растений, перемещение растений'
            ],
            [
                'name' => 'Орг расходы',
                'description' => 'Административные прочие расходы по монтажу/демонтажу'
            ],
            [
                'name' => 'Ботанический сад/Монтаж',
                'description' => 'Работы по приемке растений и материалов в Ботаническом саду'
            ],
            [
                'name' => 'Магистральная/Монтаж',
                'description' => 'Работы по приемке/отгрузке материалов на Магистральной'
            ],
            [
                'name' => 'Техзона/Уход', 
                'description' => 'Работы на Тверской тех зоне'
            ],
            [
                'name' => 'Магистральная/Уход',
                'description' => 'Работы на Магистральной с ИЮЛЯ'
            ],
        ];

        foreach ($templates as $template) {
            PurposeTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('Стандартные шаблоны назначений созданы!');
    }
}
