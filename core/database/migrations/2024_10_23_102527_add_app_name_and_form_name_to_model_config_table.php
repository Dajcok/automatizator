<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('model_config', function (Blueprint $table) {
            $table->string('app_name')->after('id');
            $table->string('form_name')->after('app_name');
            $table->unique(['app_name', 'form_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_config', function (Blueprint $table) {
            $table->dropColumn('app_name');
            $table->dropColumn('form_name');
            $table->dropUnique(['app_name', 'form_name']);
        });
    }
};
