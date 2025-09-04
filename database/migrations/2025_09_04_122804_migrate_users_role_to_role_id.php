<?php

use App\Models\Roles;
use App\Models\User;
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

        // Ensure the roles table exists first (run after create_role_table)
        if (! Schema::hasTable('roles')) {
            return;
        }

        // 1) Add role_id (nullable initially) if missing
        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->constrained('roles') // references roles.id
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }

        // 2) Backfill role_id from the old string column 'role' by matching roles.slug
        if (Schema::hasColumn('users', 'role')) {
            // Single SQL UPDATE ... JOIN is fast and safe
            DB::statement('
                UPDATE `users` u
                JOIN `roles` r ON r.`slug` = u.`role`
                SET u.`role_id` = r.`id`
                WHERE u.`role_id` IS NULL
                  AND u.`role` IS NOT NULL
                  AND u.`role` <> \'\'
            ');
        }

        // 3) Enforce NOT NULL only when all rows are filled
        if (Schema::hasColumn('users', 'role_id')) {
            $nulls = DB::table('users')->whereNull('role_id')->count();
            if ($nulls === 0) {
                // Use raw SQL to avoid requiring doctrine/dbal for change()
                DB::statement('ALTER TABLE `users` MODIFY `role_id` BIGINT UNSIGNED NOT NULL');
            }
        }

        // 4) Drop the old string column if it exists
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) Recreate the old 'role' column (nullable) if missing
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->nullable();
            });
        }

        // 2) Restore users.role from the linked roles.slug when possible
        if (Schema::hasColumn('users', 'role_id') && Schema::hasTable('roles')) {
            DB::statement('
                UPDATE `users` u
                JOIN `roles` r ON r.`id` = u.`role_id`
                SET u.`role` = r.`slug`
            ');
        }

        // 3) Drop the foreign key and column if present
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Drops the FK constraint and the column
                $table->dropConstrainedForeignId('role_id');
            });
        }
    }


};
