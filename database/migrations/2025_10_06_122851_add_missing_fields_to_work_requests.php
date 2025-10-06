<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            // Добавляем только недостающие поля
            $table->string('specialization')->after('brigadier_id');
            $table->enum('executor_type', ['our_staff', 'contractor'])->after('specialization');
            $table->integer('workers_count')->after('executor_type');
            $table->integer('shift_duration')->after('workers_count');
            $table->string('project')->after('shift_duration');
            $table->string('purpose')->after('project');
            $table->string('payer_company')->after('purpose');
            $table->text('comments')->nullable()->after('payer_company');
            $table->enum('status', [
                'draft', 'published', 'in_work', 'staffed', 
                'in_progress', 'completed', 'cancelled'
            ])->default('draft')->after('comments');
            $table->foreignId('dispatcher_id')->nullable()->constrained('users')->after('status');
            $table->timestamp('published_at')->nullable()->after('dispatcher_id');
            $table->timestamp('staffed_at')->nullable()->after('published_at');
            $table->timestamp('completed_at')->nullable()->after('staffed_at');
        });
    }

    public function down(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->dropColumn([
                'specialization',
                'executor_type',
                'workers_count',
                'shift_duration', 
                'project',
                'purpose',
                'payer_company',
                'comments',
                'status',
                'dispatcher_id',
                'published_at',
                'staffed_at', 
                'completed_at'
            ]);
        });
    }
};
