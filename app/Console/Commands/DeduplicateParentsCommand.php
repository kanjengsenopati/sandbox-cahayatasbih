<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DeduplicateParentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deduplicate-parents {--dry-run : Run the check without modifying the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicate Wali Santri accounts sharing the same phone number, transfer relations, and delete duplicates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("=== DRY RUN MODE: No database changes will be made ===");
        }

        // Get duplicate phone numbers (ignoring null, empty, and '-')
        $duplicates = User::select('phone', DB::raw('count(*) as count'))
            ->whereNotNull('phone')
            ->where('phone', '<>', '')
            ->where('phone', '<>', '-')
            ->groupBy('phone')
            ->having('count', '>', 1)
            ->orderBy('count', 'desc')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info("No duplicate phone numbers found.");
            return 0;
        }

        $this->info("Found {$duplicates->count()} duplicate phone number groups.");
        $this->info("---------------------------------------------------------");

        $totalMerged = 0;
        $totalDeleted = 0;

        foreach ($duplicates as $dup) {
            $this->comment("Processing Phone Number: {$dup->phone} (Count: {$dup->count})");

            // Load all users with this phone number
            $users = User::where('phone', $dup->phone)->get();

            // Select Primary User based on priorities:
            // 1. Has non-empty email
            // 2. Has last_login
            // 3. Earliest created_at (oldest account)
            $sortedUsers = $users->sort(function ($a, $b) {
                // Priority 1: Has email
                $aHasEmail = !empty($a->email) && filter_var($a->email, FILTER_VALIDATE_EMAIL);
                $bHasEmail = !empty($b->email) && filter_var($b->email, FILTER_VALIDATE_EMAIL);
                if ($aHasEmail !== $bHasEmail) {
                    return $bHasEmail ? 1 : -1;
                }

                // Priority 2: Has last login
                $aHasLogin = !empty($a->last_login);
                $bHasLogin = !empty($b->last_login);
                if ($aHasLogin !== $bHasLogin) {
                    return $bHasLogin ? 1 : -1;
                }

                // Priority 3: Oldest account
                return strcmp($a->created_at, $b->created_at);
            });

            $primaryUser = $sortedUsers->first();
            $duplicateUsers = $sortedUsers->slice(1);

            $this->info("  [PRIMARY] ID: {$primaryUser->id} | Name: {$primaryUser->name} | Email: " . ($primaryUser->email ?? 'N/A'));

            foreach ($duplicateUsers as $dupUser) {
                $this->warn("  [DUPLICATE TO MERGE] ID: {$dupUser->id} | Name: {$dupUser->name} | Email: " . ($dupUser->email ?? 'N/A'));

                if (!$dryRun) {
                    try {
                        DB::beginTransaction();

                        // 1. Relink students
                        $studentsCount = DB::table('students')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                        if ($studentsCount > 0) {
                            $this->line("    -> Relinked {$studentsCount} student(s) to Primary");
                        }

                        // 2. Relink notifications
                        if (Schema::hasTable('notifications')) {
                            $notifCount = DB::table('notifications')
                                ->where('user_id', $dupUser->id)
                                ->update(['user_id' => $primaryUser->id]);
                            if ($notifCount > 0) {
                                $this->line("    -> Relinked {$notifCount} notification(s)");
                            }
                        }

                        // 3. Relink ppdb_registrations
                        if (Schema::hasTable('ppdb_registrations')) {
                            $ppdbCount = DB::table('ppdb_registrations')
                                ->where('user_id', $dupUser->id)
                                ->update(['user_id' => $primaryUser->id]);
                            if ($ppdbCount > 0) {
                                $this->line("    -> Relinked {$ppdbCount} PPDB registration(s)");
                            }
                        }

                        // 4. Relink transactions
                        if (Schema::hasTable('transactions')) {
                            $txCount = DB::table('transactions')
                                ->where('user_id', $dupUser->id)
                                ->update(['user_id' => $primaryUser->id]);
                            if ($txCount > 0) {
                                $this->line("    -> Relinked {$txCount} transaction(s)");
                            }
                        }

                        // 5. Relink student_permits
                        if (Schema::hasTable('student_permits')) {
                            $permitCount = DB::table('student_permits')
                                ->where('user_id', $dupUser->id)
                                ->update(['user_id' => $primaryUser->id]);
                            if ($permitCount > 0) {
                                $this->line("    -> Relinked {$permitCount} student permit(s)");
                            }
                        }

                        // 6. Relink officers (unique user_id)
                        if (Schema::hasTable('officers')) {
                            $hasOfficer = DB::table('officers')->where('user_id', $dupUser->id)->first();
                            if ($hasOfficer) {
                                $primaryHasOfficer = DB::table('officers')->where('user_id', $primaryUser->id)->exists();
                                if (!$primaryHasOfficer) {
                                    DB::table('officers')
                                        ->where('user_id', $dupUser->id)
                                        ->update(['user_id' => $primaryUser->id]);
                                    $this->line("    -> Re-assigned Officer record to Primary");
                                } else {
                                    // Primary already has an officer record, set to null to avoid unique key violation
                                    DB::table('officers')
                                        ->where('user_id', $dupUser->id)
                                        ->update(['user_id' => null]);
                                    $this->line("    -> Reset duplicate user's Officer relation to NULL (Primary already has one)");
                                }
                            }
                        }

                        // Force Delete the duplicate user to completely clean the database
                        $dupUser->forceDelete();
                        $this->line("    -> Account ID {$dupUser->id} permanently deleted.");

                        DB::commit();
                        $totalMerged++;
                        $totalDeleted++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("    [ERROR] Failed to merge/delete ID {$dupUser->id}: " . $e->getMessage());
                        Log::error("Failed to merge duplicate user {$dupUser->id} into {$primaryUser->id}: " . $e->getMessage());
                    }
                } else {
                    $totalMerged++;
                }
            }

            $this->info("---------------------------------------------------------");
        }

        if ($dryRun) {
            $this->info("Dry Run Completed: {$totalMerged} accounts would be merged.");
        } else {
            $this->info("Deduplication Completed: Merged {$totalMerged} relations and deleted {$totalDeleted} duplicate accounts.");
        }

        return 0;
    }
}
