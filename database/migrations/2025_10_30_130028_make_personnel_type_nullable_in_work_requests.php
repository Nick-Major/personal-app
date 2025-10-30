<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->enum('personnel_type', ['personalized', 'mass'])->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->enum('personnel_type', ['personalized', 'mass'])->default('personalized')->change();
        });
    }
};
