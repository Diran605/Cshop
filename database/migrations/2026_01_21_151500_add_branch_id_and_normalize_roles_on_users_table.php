<?php

use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('role')->constrained('branches')->nullOnDelete();
        });

        DB::table('users')->where('role', 'superadmin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'branch_admin']);

        $defaultBranchId = Branch::query()->orderBy('id')->value('id');
        if ($defaultBranchId) {
            $pivot = DB::table('branch_user')
                ->select('user_id', DB::raw('MIN(branch_id) as branch_id'))
                ->groupBy('user_id')
                ->pluck('branch_id', 'user_id')
                ->all();

            $users = DB::table('users')->select('id', 'role', 'branch_id')->get();
            foreach ($users as $user) {
                if ((string) $user->role === 'super_admin') {
                    continue;
                }

                if ($user->branch_id !== null) {
                    continue;
                }

                $branchId = $pivot[$user->id] ?? $defaultBranchId;
                DB::table('users')->where('id', $user->id)->update(['branch_id' => $branchId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        DB::table('users')->where('role', 'super_admin')->update(['role' => 'superadmin']);
        DB::table('users')->where('role', 'branch_admin')->update(['role' => 'staff']);
    }
};
