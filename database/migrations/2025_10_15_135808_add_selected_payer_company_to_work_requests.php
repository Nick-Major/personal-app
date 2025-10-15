<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            // Добавляем только отсутствующую колонку
            if (!Schema::hasColumn('work_requests', 'selected_payer_company')) {
                $table->string('selected_payer_company')->nullable()->after('address_id');
            }
            
            // Удаляем старую колонку payer_company если она есть
            if (Schema::hasColumn('work_requests', 'payer_company')) {
                $table->dropColumn('payer_company');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            if (Schema::hasColumn('work_requests', 'selected_payer_company')) {
                $table->dropColumn('selected_payer_company');
            }
            
            // Восстанавливаем старую колонку при откате
            if (!Schema::hasColumn('work_requests', 'payer_company')) {
                $table->string('payer_company')->nullable();
            }
        });
    }
};
