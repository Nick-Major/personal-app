<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->text('description')->nullable()->after('name');
            $table->date('start_date')->after('description');
            $table->date('end_date')->after('start_date');
            $table->string('status')->default('planned')->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'start_date', 'end_date', 'status']);
        });
    }
};
