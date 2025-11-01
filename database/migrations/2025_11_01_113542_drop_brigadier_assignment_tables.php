<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Удаляем таблицы в правильном порядке (сначала дочерние, потом родительские)
        Schema::dropIfExists('brigadier_assignment_dates');
        Schema::dropIfExists('brigadier_assignments');
    }

    public function down()
    {
        // Восстановление таблиц в down методе обычно не делаем,
        // так как данные уже мигрированы в новую структуру
        // Но для целостности можно оставить пустым или добавить создание таблиц
    }
};
