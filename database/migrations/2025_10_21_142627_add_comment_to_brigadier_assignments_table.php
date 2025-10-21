<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('brigadier_assignments', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('can_create_requests');
        });
    }

    public function down()
    {
        Schema::table('brigadier_assignments', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};
