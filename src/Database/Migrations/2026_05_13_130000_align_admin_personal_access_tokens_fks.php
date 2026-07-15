<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrective migration for installs that received the table from the very first
 * (buggy) version of the create migration — the original used foreignId() which
 * produced BIGINT UNSIGNED columns and then failed to create FKs because admins.id
 * is INT UNSIGNED. The table was left in place without FK constraints.
 *
 * This migration:
 *   1. Drops any orphaned rows (defensive — they would block FK creation anyway)
 *   2. Coerces id-bearing columns to UNSIGNED INT to match admins.id
 *   3. Adds the FK constraints that should have been there from day one
 *
 * Fresh installs from the original migration (post hasTable-guard removal) will
 * never enter this code path because Schema::hasTable returns false on first run.
 * On those installs, this migration runs against the freshly-created table and
 * is effectively a no-op (column types already match, FKs already exist) — so
 * each step is guarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_personal_access_tokens')) {
            return;
        }

        $database = DB::getDatabaseName();

        $columnsToCoerce = [
            'admin_id' => false,   // NOT NULL
            'revoked_by_admin_id' => true,    // nullable
            'regenerated_by_admin_id' => true,
            'created_by_admin_id' => true,
        ];

        foreach ($columnsToCoerce as $column => $nullable) {
            $col = DB::selectOne(
                'SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$database, 'admin_personal_access_tokens', $column]
            );

            if (! $col) {
                continue;
            }

            // Already int unsigned → nothing to do
            if (stripos($col->COLUMN_TYPE, 'int unsigned') === 0
                && stripos($col->COLUMN_TYPE, 'bigint') !== 0
                && stripos($col->COLUMN_TYPE, 'smallint') !== 0) {
                continue;
            }

            // Convert bigint unsigned → int unsigned (no data loss since admin ids are small)
            $nullClause = $nullable ? 'NULL' : 'NOT NULL';
            DB::statement("ALTER TABLE `admin_personal_access_tokens` MODIFY `{$column}` INT UNSIGNED {$nullClause}");
        }

        // Drop orphaned rows that would break FK creation. Admin IDs that no longer
        // exist in `admins` cannot be foreign-keyed; remove those rows.
        $orphanIds = DB::table('admin_personal_access_tokens as t')
            ->leftJoin('admins as a', 'a.id', '=', 't.admin_id')
            ->whereNull('a.id')
            ->pluck('t.id')
            ->all();

        if (! empty($orphanIds)) {
            DB::table('admin_personal_access_tokens')->whereIn('id', $orphanIds)->delete();
        }

        // Null out audit columns pointing at deleted admins so they don't block nullOnDelete FKs.
        foreach (['revoked_by_admin_id', 'regenerated_by_admin_id', 'created_by_admin_id'] as $auditCol) {
            DB::table('admin_personal_access_tokens as t')
                ->leftJoin('admins as a', 'a.id', '=', "t.{$auditCol}")
                ->whereNull('a.id')
                ->whereNotNull("t.{$auditCol}")
                ->update(["t.{$auditCol}" => null]);
        }

        $existingFks = $this->getExistingForeignKeys($database);

        Schema::table('admin_personal_access_tokens', function ($table) use ($existingFks) {
            if (! in_array('admin_personal_access_tokens_admin_id_foreign', $existingFks, true)) {
                $table->foreign('admin_id', 'admin_personal_access_tokens_admin_id_foreign')
                    ->references('id')->on('admins')->cascadeOnDelete();
            }

            if (! in_array('admin_personal_access_tokens_revoked_by_admin_id_foreign', $existingFks, true)) {
                $table->foreign('revoked_by_admin_id', 'admin_personal_access_tokens_revoked_by_admin_id_foreign')
                    ->references('id')->on('admins')->nullOnDelete();
            }

            if (! in_array('admin_personal_access_tokens_regenerated_by_admin_id_foreign', $existingFks, true)) {
                $table->foreign('regenerated_by_admin_id', 'admin_personal_access_tokens_regenerated_by_admin_id_foreign')
                    ->references('id')->on('admins')->nullOnDelete();
            }

            if (! in_array('admin_personal_access_tokens_regenerated_to_id_foreign', $existingFks, true)) {
                $table->foreign('regenerated_to_id', 'admin_personal_access_tokens_regenerated_to_id_foreign')
                    ->references('id')->on('admin_personal_access_tokens')->nullOnDelete();
            }

            if (! in_array('admin_personal_access_tokens_created_by_admin_id_foreign', $existingFks, true)) {
                $table->foreign('created_by_admin_id', 'admin_personal_access_tokens_created_by_admin_id_foreign')
                    ->references('id')->on('admins')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('admin_personal_access_tokens')) {
            return;
        }

        Schema::table('admin_personal_access_tokens', function ($table) {
            foreach ([
                'admin_personal_access_tokens_admin_id_foreign',
                'admin_personal_access_tokens_revoked_by_admin_id_foreign',
                'admin_personal_access_tokens_regenerated_by_admin_id_foreign',
                'admin_personal_access_tokens_regenerated_to_id_foreign',
                'admin_personal_access_tokens_created_by_admin_id_foreign',
            ] as $fk) {
                try {
                    $table->dropForeign($fk);
                } catch (Throwable $e) {
                    // ignore — FK might not exist
                }
            }
        });
    }

    private function getExistingForeignKeys(string $database): array
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'admin_personal_access_tokens')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME')
            ->all();
    }
};
