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
            $table->date('follow_up_due_at')->nullable()->after('date_received');
            $table->tinyInteger('follow_up_stage')->default(0)->after('follow_up_due_at');
            $table->timestamp('last_notified_at')->nullable()->after('follow_up_stage');
            $table->timestamp('resolved_at')->nullable()->after('last_notified_at');
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->date('follow_up_due_at')->nullable()->after('date_received');
            $table->tinyInteger('follow_up_stage')->default(0)->after('follow_up_due_at');
            $table->timestamp('last_notified_at')->nullable()->after('follow_up_stage');
            $table->timestamp('resolved_at')->nullable()->after('last_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carpets', function (Blueprint $table) {
            $table->dropColumn([
                'follow_up_due_at',
                'follow_up_stage',
                'last_notified_at',
                'resolved_at',
            ]);
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->dropColumn([
                'follow_up_due_at',
                'follow_up_stage',
                'last_notified_at',
                'resolved_at',
            ]);
        });
    }
};
