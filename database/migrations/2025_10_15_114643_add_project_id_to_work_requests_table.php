<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('purpose_id')->nullable()->after('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->after('purpose_id')->constrained()->onDelete('cascade');
            
            // Удаляем старые текстовые поля, если они есть
            if (Schema::hasColumn('work_requests', 'project')) {
                $table->dropColumn('project');
            }
            if (Schema::hasColumn('work_requests', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['purpose_id']);
            $table->dropForeign(['address_id']);
            $table->dropColumn(['project_id', 'purpose_id', 'address_id']);
            
            // Восстанавливаем старые поля при откате
            $table->string('project')->nullable();
            $table->string('purpose')->nullable();
        });
    }
};
