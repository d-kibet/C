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
        // Indexes for Carpets table - optimized for overdue delivery queries
        Schema::table('carpets', function (Blueprint $table) {
            // Composite index for overdue delivery queries
            $table->index(['delivered', 'date_received'], 'idx_carpets_delivered_date');
            
            // Index for phone number lookups (customer analytics)
            $table->index('phone', 'idx_carpets_phone');
            
            // Index for uniqueid searches
            $table->index('uniqueid', 'idx_carpets_uniqueid');
            
            // Index for payment status queries
            $table->index('payment_status', 'idx_carpets_payment_status');
        });

        // Indexes for Laundries table
        Schema::table('laundries', function (Blueprint $table) {
            // Composite index for overdue delivery queries
            $table->index(['delivered', 'date_received'], 'idx_laundries_delivered_date');
            
            // Index for phone number lookups
            $table->index('phone', 'idx_laundries_phone');
            
            // Index for unique_id searches
            $table->index('unique_id', 'idx_laundries_unique_id');
            
            // Index for payment status queries
            $table->index('payment_status', 'idx_laundries_payment_status');
        });

        // Enhanced indexes for Notifications table (Laravel's default may not be optimal)
        Schema::table('notifications', function (Blueprint $table) {
            // Composite index for notification type and date filtering
            $table->index(['type', 'created_at'], 'idx_notifications_type_date');
            
            // Index for read status filtering
            $table->index('read_at', 'idx_notifications_read_at');
            
            // Composite index for user notifications with read status
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notifications_user_read');
        });

        // Indexes for Audit Trails table
        Schema::table('audit_trails', function (Blueprint $table) {
            // Index for event type filtering
            $table->index('event', 'idx_audit_trails_event');
            
            // Composite index for date range queries
            $table->index(['auditable_type', 'created_at'], 'idx_audit_trails_type_date');
            
            // Index for user activity queries
            $table->index(['user_id', 'created_at'], 'idx_audit_trails_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carpets', function (Blueprint $table) {
            $table->dropIndex('idx_carpets_delivered_date');
            $table->dropIndex('idx_carpets_phone');
            $table->dropIndex('idx_carpets_uniqueid');
            $table->dropIndex('idx_carpets_payment_status');
        });

        Schema::table('laundries', function (Blueprint $table) {
            $table->dropIndex('idx_laundries_delivered_date');
            $table->dropIndex('idx_laundries_phone');
            $table->dropIndex('idx_laundries_unique_id');
            $table->dropIndex('idx_laundries_payment_status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_type_date');
            $table->dropIndex('idx_notifications_read_at');
            $table->dropIndex('idx_notifications_user_read');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropIndex('idx_audit_trails_event');
            $table->dropIndex('idx_audit_trails_type_date');
            $table->dropIndex('idx_audit_trails_user_date');
        });
    }
};