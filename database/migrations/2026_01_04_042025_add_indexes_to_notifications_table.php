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
            // Performance indexes for common queries
            $table->index('type', 'notifications_type_index');
            $table->index('read_at', 'notifications_read_at_index');
            $table->index('created_at', 'notifications_created_at_index');

            // Composite index for most common query pattern: unread notifications by type
            $table->index(['notifiable_id', 'read_at', 'created_at'], 'notifications_unread_composite_index');

            // Index for type + created_at (common in overdue queries)
            $table->index(['type', 'created_at'], 'notifications_type_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_type_index');
            $table->dropIndex('notifications_read_at_index');
            $table->dropIndex('notifications_created_at_index');
            $table->dropIndex('notifications_unread_composite_index');
            $table->dropIndex('notifications_type_created_index');
        });
    }
};
