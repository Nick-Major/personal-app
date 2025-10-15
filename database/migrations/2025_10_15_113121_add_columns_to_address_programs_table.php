<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address_programs', function (Blueprint $table) {
            $table->foreignId('project_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->after('project_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(1)->after('address_id');
            $table->boolean('is_active')->default(true)->after('order');
            
            $table->unique(['project_id', 'address_id']);
        });
    }

    public function down(): void
    {
        Schema::table('address_programs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['address_id']);
            $table->dropColumn(['project_id', 'address_id', 'order', 'is_active']);
        });
    }
};
