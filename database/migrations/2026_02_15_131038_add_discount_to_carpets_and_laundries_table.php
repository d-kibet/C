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
        Schema::table('carpets', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->nullable()->default(0)->after('price');
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->nullable()->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carpets', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
