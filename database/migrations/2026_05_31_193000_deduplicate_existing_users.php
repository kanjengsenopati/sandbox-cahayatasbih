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
        // Get duplicate phone numbers (ignoring null, empty, and '-')
        $duplicates = User::withTrashed()
            ->select('phone', DB::raw('count(*) as count'))
            ->whereNotNull('phone')
            ->where('phone', '<>', '')
            ->where('phone', '<>', '-')
            ->groupBy('phone')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            // Load all users (including soft-deleted) with this phone number
            $users = User::withTrashed()->where('phone', $dup->phone)->get();

            // Select Primary User based on priorities:
            // 1. Not deleted (active)
            // 2. Has non-empty email
            // 3. Has last_login
            // 4. Earliest created_at (oldest account)
            $sortedUsers = $users->sort(function ($a, $b) {
                // Priority 0: Active accounts over soft-deleted
                $aDeleted = !is_null($a->deleted_at);
                $bDeleted = !is_null($b->deleted_at);
                if ($aDeleted !== $bDeleted) {
                    return $aDeleted ? 1 : -1;
                }

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

            // Make sure the primary user is restored if it was soft-deleted but we need to use it
            if ($primaryUser->deleted_at) {
                $primaryUser->restore();
            }

            foreach ($duplicateUsers as $dupUser) {
                try {
                    DB::beginTransaction();

                    // 1. Relink students
                    DB::table('students')
                        ->where('user_id', $dupUser->id)
                        ->update(['user_id' => $primaryUser->id]);

                    // 2. Relink notifications
                    if (Schema::hasTable('notifications')) {
                        DB::table('notifications')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }

                    // 3. Relink ppdb_registrations
                    if (Schema::hasTable('ppdb_registrations')) {
                        DB::table('ppdb_registrations')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }

                    // 4. Relink transactions
                    if (Schema::hasTable('transactions')) {
                        DB::table('transactions')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }

                    // 5. Relink student_permits
                    if (Schema::hasTable('student_permits')) {
                        DB::table('student_permits')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }

                    // 6. Relink oauth relations
                    if (Schema::hasTable('oauth_access_tokens')) {
                        DB::table('oauth_access_tokens')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }
                    if (Schema::hasTable('oauth_auth_codes')) {
                        DB::table('oauth_auth_codes')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }
                    if (Schema::hasTable('oauth_clients')) {
                        DB::table('oauth_clients')
                            ->where('user_id', $dupUser->id)
                            ->update(['user_id' => $primaryUser->id]);
                    }

                    // 7. Relink officers (handling unique key)
                    if (Schema::hasTable('officers')) {
                        $hasOfficer = DB::table('officers')->where('user_id', $dupUser->id)->first();
                        if ($hasOfficer) {
                            $primaryHasOfficer = DB::table('officers')->where('user_id', $primaryUser->id)->exists();
                            if (!$primaryHasOfficer) {
                                DB::table('officers')
                                    ->where('user_id', $dupUser->id)
                                    ->update(['user_id' => $primaryUser->id]);
                            } else {
                                DB::table('officers')
                                    ->where('user_id', $dupUser->id)
                                    ->update(['user_id' => null]);
                            }
                        }
                    }

                    // Force Delete duplicate user
                    $dupUser->forceDelete();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Failed to merge duplicate user {$dupUser->id} into {$primaryUser->id} in migration: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Permanently merged, cannot be safely rolled back
    }
};
