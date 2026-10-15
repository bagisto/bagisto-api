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
        if (
            ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
            || ! Schema::hasTable('admin_personal_access_tokens')
        ) {
            return;
        }

        $prefixedTable = DB::getTablePrefix().'admin_personal_access_tokens';

        $columns = collect(Schema::getColumns('admin_personal_access_tokens'))->keyBy('name');

        foreach ([
            'admin_id' => false,
            'revoked_by_admin_id' => true,
            'regenerated_by_admin_id' => true,
            'created_by_admin_id' => true,
        ] as $column => $nullable) {
            $columnInfo = $columns->get($column);

            if (
                ! $columnInfo
                || (
                    strtolower($columnInfo['type_name']) === 'int'
                    && str_contains(strtolower($columnInfo['type']), 'unsigned')
                )
            ) {
                continue;
            }

            $nullClause = $nullable ? 'NULL' : 'NOT NULL';

            DB::statement("ALTER TABLE `{$prefixedTable}` MODIFY `{$column}` INT UNSIGNED {$nullClause}");
        }

        $orphanIds = DB::table('admin_personal_access_tokens as t')
            ->leftJoin('admins as a', 'a.id', '=', 't.admin_id')
            ->whereNull('a.id')
            ->pluck('t.id')
            ->all();

        if (! empty($orphanIds)) {
            DB::table('admin_personal_access_tokens')->whereIn('id', $orphanIds)->delete();
        }

        foreach (['revoked_by_admin_id', 'regenerated_by_admin_id', 'created_by_admin_id'] as $auditCol) {
            DB::table('admin_personal_access_tokens as t')
                ->leftJoin('admins as a', 'a.id', '=', "t.{$auditCol}")
                ->whereNull('a.id')
                ->whereNotNull("t.{$auditCol}")
                ->update(["t.{$auditCol}" => null]);
        }

        $constrainedColumns = collect(Schema::getForeignKeys('admin_personal_access_tokens'))
            ->flatMap(fn ($foreignKey) => $foreignKey['columns'])
            ->all();

        Schema::table('admin_personal_access_tokens', function ($table) use ($constrainedColumns) {
            foreach ($this->foreignKeys() as $column => [$references, $onDelete]) {
                if (in_array($column, $constrainedColumns, true)) {
                    continue;
                }

                $foreign = $table->foreign($column)->references('id')->on($references);

                $onDelete === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('admin_personal_access_tokens')) {
            return;
        }

        Schema::table('admin_personal_access_tokens', function ($table) {
            foreach (array_keys($this->foreignKeys()) as $column) {
                $this->dropForeignKeyIfPresent($table, $column);
            }
        });
    }

    /**
     * Drop one foreign key, ignoring a missing constraint.
     */
    private function dropForeignKeyIfPresent($table, string $column): void
    {
        try {
            $table->dropForeign([$column]);
        } catch (Throwable $exception) {
        }
    }

    /**
     * Foreign keys the table must carry: column => [referenced table, on-delete action].
     */
    private function foreignKeys(): array
    {
        return [
            'admin_id' => ['admins', 'cascade'],
            'revoked_by_admin_id' => ['admins', 'null'],
            'regenerated_by_admin_id' => ['admins', 'null'],
            'regenerated_to_id' => ['admin_personal_access_tokens', 'null'],
            'created_by_admin_id' => ['admins', 'null'],
        ];
    }
};
