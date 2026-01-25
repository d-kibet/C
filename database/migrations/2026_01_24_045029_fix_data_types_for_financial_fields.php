<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix financial fields from string to decimal for data integrity.
     */
    public function up(): void
    {
        // Disable strict mode temporarily for this session
        DB::statement("SET SESSION sql_mode = ''");

        // ============================================
        // STEP 1: Clean invalid data before conversion
        // ============================================

        // Clean CARPETS table - price (set invalid values to NULL)
        DB::statement("UPDATE carpets SET price = CASE
            WHEN price IS NULL THEN NULL
            WHEN TRIM(CAST(price AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(price AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(price AS CHAR))
        END");

        // Clean LAUNDRIES table
        DB::statement("UPDATE laundries SET price = CASE
            WHEN price IS NULL THEN NULL
            WHEN TRIM(CAST(price AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(price AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(price AS CHAR))
        END");

        DB::statement("UPDATE laundries SET total = CASE
            WHEN total IS NULL THEN NULL
            WHEN TRIM(CAST(total AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(total AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(total AS CHAR))
        END");

        DB::statement("UPDATE laundries SET weight = CASE
            WHEN weight IS NULL THEN NULL
            WHEN TRIM(CAST(weight AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(weight AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(weight AS CHAR))
        END");

        DB::statement("UPDATE laundries SET quantity = CASE
            WHEN quantity IS NULL THEN NULL
            WHEN TRIM(CAST(quantity AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(quantity AS CHAR)) NOT REGEXP '^[0-9]+$' THEN NULL
            ELSE TRIM(CAST(quantity AS CHAR))
        END");

        // Clean MPESAS table
        DB::statement("UPDATE mpesas SET cash = CASE
            WHEN cash IS NULL THEN NULL
            WHEN TRIM(CAST(cash AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(cash AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(cash AS CHAR))
        END");

        DB::statement("UPDATE mpesas SET `float` = CASE
            WHEN `float` IS NULL THEN NULL
            WHEN TRIM(CAST(`float` AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(`float` AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(`float` AS CHAR))
        END");

        DB::statement("UPDATE mpesas SET working = CASE
            WHEN working IS NULL THEN NULL
            WHEN TRIM(CAST(working AS CHAR)) = '' THEN NULL
            WHEN TRIM(CAST(working AS CHAR)) NOT REGEXP '^[0-9]+\\.?[0-9]*$' THEN NULL
            ELSE TRIM(CAST(working AS CHAR))
        END");

        // ============================================
        // STEP 2: Convert columns to proper data types
        // ============================================

        // Carpets - price should be decimal
        DB::statement('ALTER TABLE carpets MODIFY price DECIMAL(10,2) NULL');

        // Laundries - price, total, weight should be decimal, quantity should be integer
        DB::statement('ALTER TABLE laundries MODIFY price DECIMAL(10,2) NULL');
        DB::statement('ALTER TABLE laundries MODIFY total DECIMAL(10,2) NULL');
        DB::statement('ALTER TABLE laundries MODIFY weight DECIMAL(8,2) NULL');
        DB::statement('ALTER TABLE laundries MODIFY quantity INT UNSIGNED NULL');

        // Mpesas - cash, float, working should be decimal
        DB::statement('ALTER TABLE mpesas MODIFY cash DECIMAL(10,2) NULL');
        DB::statement("ALTER TABLE mpesas MODIFY `float` DECIMAL(10,2) NULL");
        DB::statement('ALTER TABLE mpesas MODIFY working DECIMAL(10,2) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert carpets
        DB::statement('ALTER TABLE carpets MODIFY price VARCHAR(200) NULL');

        // Revert laundries
        DB::statement('ALTER TABLE laundries MODIFY price VARCHAR(200) NULL');
        DB::statement('ALTER TABLE laundries MODIFY total VARCHAR(200) NULL');
        DB::statement('ALTER TABLE laundries MODIFY weight VARCHAR(200) NULL');
        DB::statement('ALTER TABLE laundries MODIFY quantity VARCHAR(200) NULL');

        // Revert mpesas
        DB::statement('ALTER TABLE mpesas MODIFY cash VARCHAR(255) NULL');
        DB::statement("ALTER TABLE mpesas MODIFY `float` VARCHAR(255) NULL");
        DB::statement('ALTER TABLE mpesas MODIFY working VARCHAR(255) NULL');
    }
};
