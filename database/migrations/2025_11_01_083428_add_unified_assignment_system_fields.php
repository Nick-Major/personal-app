<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Добавляем поля в brigadier_assignment_dates
        Schema::table('brigadier_assignment_dates', function (Blueprint $table) {
            $table->time('planned_start_time')->nullable()->comment('Планируемое время начала работы');
            $table->decimal('planned_duration_hours', 4, 1)->nullable()->comment('Планируемая продолжительность смены (часов)');
            $table->string('assignment_number')->nullable()->comment('Уникальный номер назначения');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->comment('Созданная смена');
            $table->text('date_comment')->nullable()->comment('Комментарий для конкретной даты');
            
            // Индексы для поиска
            $table->index(['assignment_date', 'status']);
            $table->index('assignment_number');
        });

        // 2. Добавляем assignment_number в shifts
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('assignment_number')->nullable()->comment('Номер назначения для бригадиров');
            $table->index('assignment_number');
        });

        // 3. Добавляем assignment_number в assignments
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('assignment_number')->nullable()->comment('Уникальный номер назначения для бригадиров');
            $table->index('assignment_number');
        });
    }

    public function down()
    {
        Schema::table('brigadier_assignment_dates', function (Blueprint $table) {
            $table->dropColumn([
                'planned_start_time',
                'planned_duration_hours', 
                'assignment_number',
                'shift_id',
                'date_comment'
            ]);
            
            $table->dropIndex(['assignment_date', 'status']);
            $table->dropIndex(['assignment_number']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('assignment_number');
            $table->dropIndex(['assignment_number']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('assignment_number');
            $table->dropIndex(['assignment_number']);
        });
    }
};
