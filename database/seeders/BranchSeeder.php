<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Create the two branches
        $main = Branch::firstOrCreate(
            ['code' => 'NBO'],
            [
                'name'      => 'Main Branch',
                'location'  => 'Nairobi',
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'MSA'],
            [
                'name'      => 'Mombasa Branch',
                'location'  => 'Mombasa',
                'is_active' => true,
            ]
        );

        // Backfill all existing records to the main branch so nothing is orphaned.
        // New records created after this migration will carry the user's branch_id automatically.
        foreach (['carpets', 'laundries', 'orders', 'mpesas', 'expenses'] as $table) {
            DB::table($table)->whereNull('branch_id')->update(['branch_id' => $main->id]);
        }

        // Assign all existing users (except any with null branch_id that are SuperAdmins)
        // to the main branch only if they have no branch yet.
        // SuperAdmins should be set to null manually via the admin panel.
        DB::table('users')->whereNull('branch_id')->update(['branch_id' => $main->id]);

        $this->command->info("Branches seeded. All existing records assigned to '{$main->name}' (id={$main->id}).");
        $this->command->warn("ACTION REQUIRED: Set SuperAdmin users to branch_id = NULL via the admin panel.");
    }
}
