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
        Schema::create('laundries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('location');
            $table->string('unique_id')->nullable();
            $table->date('date_received')->nullable();
            $table->date('date_delivered')->nullable();
            $table->string('quantity');
            $table->string('item_description');
            $table->string('weight')->nullable();
            $table->string('price');
            $table->string('total');
            $table->string('delivered')->nullable();
            $table->string('payment_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundries');
    }
};




