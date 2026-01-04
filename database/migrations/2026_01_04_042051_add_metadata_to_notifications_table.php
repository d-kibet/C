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
        Schema::table('notifications', function (Blueprint $table) {
            // TTL - Time to live (auto-expire old notifications)
            $table->timestamp('expires_at')->nullable()->after('read_at');

            // Track how many times this notification was updated
            $table->unsignedInteger('update_count')->default(0)->after('expires_at');

            // Track last update time for consolidated notifications
            $table->timestamp('last_updated_at')->nullable()->after('update_count');

            // Flag for auto-generated vs manual notifications
            $table->boolean('is_automated')->default(true)->after('last_updated_at');

            // Priority level for notification sorting
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('is_automated');

            // Add index on expires_at for efficient cleanup
            $table->index('expires_at', 'notifications_expires_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_expires_at_index');
            $table->dropColumn([
                'expires_at',
                'update_count',
                'last_updated_at',
                'is_automated',
                'priority'
            ]);
        });
    }
};
