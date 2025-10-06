<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('contact_person')->after('name');
            $table->string('phone')->after('contact_person');
            $table->string('email')->after('phone');
            $table->json('specializations')->after('email');
            $table->boolean('is_active')->default(true)->after('specializations');
        });
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'contact_person', 
                'phone',
                'email',
                'specializations',
                'is_active'
            ]);
        });
    }
};
