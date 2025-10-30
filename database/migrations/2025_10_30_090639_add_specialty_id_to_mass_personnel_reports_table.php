<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mass_personnel_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('specialty_id')->nullable()->after('category_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('mass_personnel_reports', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropColumn('specialty_id');
        });
    }
};
