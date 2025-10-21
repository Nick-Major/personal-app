<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('brigadier_assignment_dates', function (Blueprint $table) {
            // Удаляем старый внешний ключ если есть
            $table->dropForeign(['assignment_id']);
            
            // Добавляем новый с каскадным удалением
            $table->foreign('assignment_id')
                  ->references('id')
                  ->on('brigadier_assignments')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('brigadier_assignment_dates', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            
            // Возвращаем старый внешний ключ без каскада
            $table->foreign('assignment_id')
                  ->references('id')
                  ->on('brigadier_assignments');
        });
    }
};
