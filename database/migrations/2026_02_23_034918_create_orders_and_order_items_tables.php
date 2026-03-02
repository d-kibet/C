<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->enum('type', ['carpet', 'laundry']);
            $table->string('name', 200);
            $table->string('phone', 15);
            $table->string('location', 400)->nullable();
            $table->date('date_received');
            $table->date('date_delivered')->nullable();
            $table->enum('payment_status', ['Paid', 'Partial', 'Not Paid'])->default('Not Paid');
            $table->string('transaction_code', 255)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('follow_up_due_at')->nullable();
            $table->tinyInteger('follow_up_stage')->default(0);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('phone');
            $table->index('date_received');
            $table->index('payment_status');
            $table->index('type');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('unique_id', 200)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('item_total', 10, 2)->default(0);
            $table->enum('delivered', ['Delivered', 'Not Delivered'])->default('Not Delivered');
            $table->date('date_delivered')->nullable();

            // Carpet-specific fields
            $table->string('size', 200)->nullable();
            $table->decimal('multiplier', 10, 2)->nullable();

            // Laundry-specific fields
            $table->integer('quantity')->nullable();
            $table->text('item_description')->nullable();
            $table->decimal('weight', 10, 2)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('order_id');
            $table->index('unique_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
