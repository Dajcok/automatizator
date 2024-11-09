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
            $table->json('filter_config')->nullable();
            $table->string('form_type')->after('column_config')->default('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_config', function (Blueprint $table) {
            $table->dropColumn('filter_config');
            $table->dropColumn('form_type');
        });
    }
};
