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
        Schema::create('carpets', function (Blueprint $table) {
            $table->id();
            $table->string('uniqueid');
            $table->string('size');
            $table->string('price');
            $table->string('phone');
            $table->string('location')->nullable();
            $table->string('payment_status');
            $table->string('delivered');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpets');
    }
};
