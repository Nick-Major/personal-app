<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('brigadier_assignments', function (Blueprint $table) {
            $table->boolean('can_create_requests')->default(false)->after('initiator_id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('can_create_requests');
        });
    }

    public function down()
    {
        Schema::table('brigadier_assignments', function (Blueprint $table) {
            $table->dropColumn(['can_create_requests', 'status']);
        });
    }
};
