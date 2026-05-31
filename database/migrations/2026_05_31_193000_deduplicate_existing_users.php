<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Get duplicate phone numbers (ignoring null, empty, and '-')
        $duplicates = User::withTrashed()
            ->select('phone', DB::raw('count(*) as count'))
            ->whereNotNull('phone')
            ->where('phone', '<>', '')
            ->where('phone', '<>', '-')
            ->groupBy('phone')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            return;
        }

        $duplicateIdsToDelete = [];
        $mapping = []; // [duplicate_id => primary_id]
        $usersToRestore = [];

        // 2. Map duplicates to primary
        foreach ($duplicates as $dup) {
            $users = User::withTrashed()->where('phone', $dup->phone)->get();

            // Select Primary User based on priorities
            $sortedUsers = $users->sort(function ($a, $b) {
                $aDeleted = !is_null($a->deleted_at);
                $bDeleted = !is_null($b->deleted_at);
                if ($aDeleted !== $bDeleted) {
                    return $aDeleted ? 1 : -1;
                }
                $aHasEmail = !empty($a->email) && filter_var($a->email, FILTER_VALIDATE_EMAIL);
                $bHasEmail = !empty($b->email) && filter_var($b->email, FILTER_VALIDATE_EMAIL);
                if ($aHasEmail !== $bHasEmail) {
                    return $bHasEmail ? 1 : -1;
                }
                $aHasLogin = !empty($a->last_login);
                $bHasLogin = !empty($b->last_login);
                if ($aHasLogin !== $bHasLogin) {
                    return $bHasLogin ? 1 : -1;
                }
                return strcmp($a->created_at, $b->created_at);
            });

            $primaryUser = $sortedUsers->first();
            $duplicateUsers = $sortedUsers->slice(1);

            if ($primaryUser->deleted_at) {
                $usersToRestore[] = $primaryUser->id;
            }

            foreach ($duplicateUsers as $dupUser) {
                $mapping[$dupUser->id] = $primaryUser->id;
                $duplicateIdsToDelete[] = $dupUser->id;
            }
        }

        if (empty($duplicateIdsToDelete)) {
            return;
        }

        // 3. Restore primary users in bulk if they were soft-deleted but chosen as primary
        if (!empty($usersToRestore)) {
            User::withTrashed()->whereIn('id', $usersToRestore)->restore();
        }

        // 4. Relink relations in bulk by checking which duplicate IDs actually have relations in each table
        $tables = [
            'students',
            'notifications',
            'ppdb_registrations',
            'transactions',
            'student_permits',
            'oauth_access_tokens',
            'oauth_auth_codes',
            'oauth_clients'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // Find duplicate user IDs that actually have records in this table
                $activeDupIds = DB::table($table)
                    ->whereIn('user_id', $duplicateIdsToDelete)
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                foreach ($activeDupIds as $dupId) {
                    $primaryId = $mapping[$dupId] ?? null;
                    if ($primaryId) {
                        try {
                            DB::table($table)->where('user_id', $dupId)->update(['user_id' => $primaryId]);
                        } catch (\Exception $e) {
                            Log::error("Failed to update user_id in table {$table} from {$dupId} to {$primaryId}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        // 5. Handle officers (special unique user_id constraint)
        if (Schema::hasTable('officers')) {
            $officersWithDupUsers = DB::table('officers')
                ->whereIn('user_id', $duplicateIdsToDelete)
                ->get();

            foreach ($officersWithDupUsers as $officer) {
                $dupId = $officer->user_id;
                $primaryId = $mapping[$dupId] ?? null;
                if ($primaryId) {
                    $primaryHasOfficer = DB::table('officers')->where('user_id', $primaryId)->exists();
                    if (!$primaryHasOfficer) {
                        try {
                            DB::table('officers')->where('user_id', $dupId)->update(['user_id' => $primaryId]);
                        } catch (\Exception $e) {
                            Log::error("Failed to update officer relation for duplicate {$dupId}: " . $e->getMessage());
                        }
                    } else {
                        try {
                            DB::table('officers')->where('user_id', $dupId)->update(['user_id' => null]);
                        } catch (\Exception $e) {
                            Log::error("Failed to nullify duplicate officer relation for duplicate {$dupId}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        // 6. Force delete duplicates in bulk
        User::withTrashed()->whereIn('id', $duplicateIdsToDelete)->forceDelete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Permanently merged, cannot be safely rolled back
    }
};
