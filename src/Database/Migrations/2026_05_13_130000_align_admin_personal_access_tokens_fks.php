<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs tables created by the first version of the create migration, which made the admin
 * columns BIGINT and left them without foreign keys. A no-op on a table the current create
 * migration built. `information_schema` is read with raw SQL because the query builder would
 * prefix its table names.
 */
return new class extends Migration
{
    /**
     * Foreign keys this table needs: column => [referenced table, nullable, on delete].
     */
    private const FOREIGN_KEYS = [
        'admin_id' => ['admins', false, 'cascade'],
        'revoked_by_admin_id' => ['admins', true, 'set null'],
        'regenerated_by_admin_id' => ['admins', true, 'set null'],
        'regenerated_to_id' => ['admin_personal_access_tokens', true, 'set null'],
        'created_by_admin_id' => ['admins', true, 'set null'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('admin_personal_access_tokens')) {
            return;
        }

        $prefix = DB::getTablePrefix();
        $database = DB::getDatabaseName();
        $table = $prefix.'admin_personal_access_tokens';

        foreach (self::FOREIGN_KEYS as $column => [$references, $nullable]) {
            if ($references !== 'admins') {
                continue;
            }

            $type = DB::selectOne(
                'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$database, $table, $column]
            )?->COLUMN_TYPE;

            if ($type === null || preg_match('/^int(\(\d+\))? unsigned/i', $type)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` INT UNSIGNED %s',
                $table,
                $column,
                $nullable ? 'NULL' : 'NOT NULL'
            ));
        }

        $orphanIds = DB::table('admin_personal_access_tokens as t')
            ->leftJoin('admins as a', 'a.id', '=', 't.admin_id')
            ->whereNull('a.id')
            ->pluck('t.id')
            ->all();

        if (! empty($orphanIds)) {
            DB::table('admin_personal_access_tokens')->whereIn('id', $orphanIds)->delete();
        }

        foreach (['revoked_by_admin_id', 'regenerated_by_admin_id', 'created_by_admin_id'] as $column) {
            DB::table('admin_personal_access_tokens as t')
                ->leftJoin('admins as a', 'a.id', '=', "t.{$column}")
                ->whereNull('a.id')
                ->whereNotNull("t.{$column}")
                ->update(["t.{$column}" => null]);
        }

        $constrained = array_map(
            fn (object $row) => $row->COLUMN_NAME,
            DB::select(
                'SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$database, $table]
            )
        );

        $missing = array_diff_key(self::FOREIGN_KEYS, array_flip($constrained));

        if (empty($missing)) {
            return;
        }

        Schema::table('admin_personal_access_tokens', function (Blueprint $blueprint) use ($missing, $prefix) {
            foreach ($missing as $column => [$references, , $onDelete]) {
                $blueprint->foreign($column, $prefix.'admin_pat_'.$column.'_foreign')
                    ->references('id')
                    ->on($references)
                    ->onDelete($onDelete);
            }
        });
    }

    /**
     * The create migration owns these foreign keys and drops them with the table.
     */
    public function down(): void {}
};
