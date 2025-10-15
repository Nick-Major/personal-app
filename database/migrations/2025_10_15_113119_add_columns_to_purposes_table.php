<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purposes', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->text('description')->nullable()->after('name');
            $table->string('category')->default('other')->after('description');
            $table->boolean('is_active')->default(true)->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('purposes', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'category', 'is_active']);
        });
    }
};
