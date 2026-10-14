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
        // Add branch_id to users — nullable means SuperAdmin (no branch restriction)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('status')
                  ->constrained('branches')->nullOnDelete();
            $table->index('branch_id');
        });

        // Data tables: default all existing records to branch 1 (Main Branch)
        $tables = ['carpets', 'laundries', 'orders', 'mpesas', 'expenses'];
        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')
                      ->constrained('branches')->nullOnDelete();
                $table->index('branch_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });

        foreach (['carpets', 'laundries', 'orders', 'mpesas', 'expenses'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropForeign(['branch_id']);
                $table->dropIndex(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
