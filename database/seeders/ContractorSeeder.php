<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contractor;

class ContractorSeeder extends Seeder
{
    public function run(): void
    {
        $contractors = [
            [
                'name' => 'ООО "СтройСервис"',
                'contact_person' => 'Сергей Петров',
                'phone' => '+7 (495) 123-45-67',
                'email' => 'info@stroy-service.ru',
                'specializations' => ['садовники', 'специалисты по озеленению', 'установщик деревьев'],
                'is_active' => true,
            ],
            [
                'name' => 'Агрокомплекс "Зеленый Мир"',
                'contact_person' => 'Анна Козлова', 
                'phone' => '+7 (495) 234-56-78',
                'email' => 'contact@greenworld.ru',
                'specializations' => ['садовники (хим. обработка)', 'помощник садовника', 'штатные специалисты'],
                'is_active' => true,
            ],
            [
                'name' => 'Декоративные Решения',
                'contact_person' => 'Дмитрий Семенов',
                'phone' => '+7 (495) 345-67-89',
                'email' => 'decor@decorsolutions.ru',
                'specializations' => ['декораторы', 'старшие декораторы', 'администраторы'],
                'is_active' => true,
            ],
            [
                'name' => 'ПрофСервис Монтаж',
                'contact_person' => 'Ольга Николаева',
                'phone' => '+7 (495) 456-78-90',
                'email' => 'mount@profservice.ru',
                'specializations' => ['установщик деревьев', 'специалисты по озеленению', 'старшие садовники'],
                'is_active' => true,
            ],
        ];

        foreach ($contractors as $contractor) {
            Contractor::create($contractor);
        }
    }
}
