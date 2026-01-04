<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
        });

        // Use raw SQL to modify columns to be nullable
        DB::statement('ALTER TABLE expenses MODIFY created_by BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE expenses MODIFY approved_by BIGINT UNSIGNED NULL');

        Schema::table('expenses', function (Blueprint $table) {
            // Re-add foreign keys with SET NULL on delete
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop the SET NULL foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
        });

        // Restore original column constraints (created_by NOT NULL, approved_by NULL)
        DB::statement('ALTER TABLE expenses MODIFY created_by BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE expenses MODIFY approved_by BIGINT UNSIGNED NULL');

        Schema::table('expenses', function (Blueprint $table) {
            // Re-add the original restrictive foreign keys
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users');

            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users');
        });
    }
};
