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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Channel preferences
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('database_enabled')->default(true);

            // Notification type preferences
            $table->boolean('overdue_notifications')->default(true);
            $table->boolean('payment_reminders')->default(true);
            $table->boolean('pickup_notifications')->default(true);
            $table->boolean('followup_reminders')->default(true);

            // Quiet hours (do not disturb)
            $table->time('quiet_hours_start')->nullable(); // e.g., 22:00
            $table->time('quiet_hours_end')->nullable();   // e.g., 08:00

            // Frequency controls
            $table->unsignedInteger('overdue_notification_interval')->default(5); // days
            $table->unsignedInteger('max_notifications_per_day')->default(50);

            // Digest preferences (batch notifications)
            $table->boolean('daily_digest')->default(false);
            $table->time('daily_digest_time')->nullable(); // e.g., 09:00

            $table->timestamps();

            // Unique constraint - one preference per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
