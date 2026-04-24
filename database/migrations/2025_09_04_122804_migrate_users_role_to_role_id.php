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
            if (DB::connection()->getDriverName() === 'sqlite') {
                // SQLite does not support UPDATE ... JOIN syntax
                $rows = DB::table('users')
                    ->join('roles', 'roles.slug', '=', 'users.role')
                    ->whereNull('users.role_id')
                    ->whereNotNull('users.role')
                    ->where('users.role', '<>', '')
                    ->select('users.id as uid', 'roles.id as rid')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('users')->where('id', $row->uid)->update(['role_id' => $row->rid]);
                }
            } else {
                DB::statement('
                    UPDATE `users` u
                    JOIN `roles` r ON r.`slug` = u.`role`
                    SET u.`role_id` = r.`id`
                    WHERE u.`role_id` IS NULL
                      AND u.`role` IS NOT NULL
                      AND u.`role` <> \'\'
                ');
            }
        }

        // 3) Enforce NOT NULL only when all rows are filled
        if (Schema::hasColumn('users', 'role_id')) {
            $nulls = DB::table('users')->whereNull('role_id')->count();
            if ($nulls === 0) {
                // Drop the foreign key constraint that might have SET NULL
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['role_id']);
                });

                if (DB::connection()->getDriverName() === 'sqlite') {
                    // SQLite doesn't support MODIFY, so we skip enforcing NOT NULL in-memory
                    // (the column is already effectively NOT NULL since all rows have values)
                } else {
                    // Use raw SQL to avoid requiring doctrine/dbal for change()
                    DB::statement('ALTER TABLE `users` MODIFY `role_id` BIGINT UNSIGNED NOT NULL');
                }

                // Re-add the foreign key constraint without SET NULL
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('role_id')
                        ->references('id')
                        ->on('roles')
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();
                });
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
            if (DB::connection()->getDriverName() === 'sqlite') {
                $rows = DB::table('users')
                    ->join('roles', 'roles.id', '=', 'users.role_id')
                    ->select('users.id as uid', 'roles.slug as slug')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('users')->where('id', $row->uid)->update(['role' => $row->slug]);
                }
            } else {
                DB::statement('
                    UPDATE `users` u
                    JOIN `roles` r ON r.`id` = u.`role_id`
                    SET u.`role` = r.`slug`
                ');
            }
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
