<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Создаем таблицу для дат назначения
        Schema::create('brigadier_assignment_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('brigadier_assignments')->onDelete('cascade');
            $table->date('assignment_date');
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'assignment_date']);
        });

        // Обновляем основную таблицу (убираем date поля)
        Schema::table('brigadier_assignments', function (Blueprint $table) {
            $table->dropColumn(['assignment_date', 'status', 'confirmed_at', 'rejected_at', 'rejection_reason']);
        });
    }

    public function down(): void
    {
        // Восстанавливаем основную таблицу
        Schema::table('brigadier_assignments', function (Blueprint $table) {
            $table->date('assignment_date');
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });

        Schema::dropIfExists('brigadier_assignment_dates');
    }
};
