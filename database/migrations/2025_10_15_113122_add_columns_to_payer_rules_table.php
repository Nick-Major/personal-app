<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payer_rules', function (Blueprint $table) {
            $table->foreignId('purpose_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->after('purpose_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_program_id')->nullable()->after('address_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->after('address_program_id')->constrained()->onDelete('cascade');
            $table->string('payer_company')->after('project_id');
            $table->integer('priority')->default(1)->after('payer_company');
            $table->text('description')->nullable()->after('priority');
            $table->boolean('is_custom')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('payer_rules', function (Blueprint $table) {
            $table->dropForeign(['purpose_id']);
            $table->dropForeign(['address_id']);
            $table->dropForeign(['address_program_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'purpose_id', 'address_id', 'address_program_id', 'project_id',
                'payer_company', 'priority', 'description', 'is_custom'
            ]);
        });
    }
};
