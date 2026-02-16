<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carpets', function (Blueprint $table) {
            $table->index('phone');
            $table->index('uniqueid');
            $table->index('date_received');
            $table->index('payment_status');
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->index('phone');
            $table->index('unique_id');
            $table->index('date_received');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('carpets', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['uniqueid']);
            $table->dropIndex(['date_received']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['unique_id']);
            $table->dropIndex(['date_received']);
            $table->dropIndex(['payment_status']);
        });
    }
};
