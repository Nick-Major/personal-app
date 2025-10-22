<?php
// database/migrations/2025_10_22_141022_create_shift_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shift_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('transport_fee', 10, 2)->default(0);
            $table->integer('no_lunch_bonus_hours')->default(1);
            $table->timestamps();
        });

        // Создаем запись по умолчанию
        \App\Models\ShiftSetting::create([
            'transport_fee' => 0,
            'no_lunch_bonus_hours' => 1
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('shift_settings');
    }
};
