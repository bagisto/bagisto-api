<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Coerce the admin id columns to unsigned int, drop orphaned rows and add the missing foreign keys.
     */
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

    /**
     * Drop the foreign keys this migration manages that are present on the table.
     */
    public function down(): void
    {
        if (! Schema::hasTable('admin_personal_access_tokens')) {
            return;
        }

        $constrainedColumns = collect(Schema::getForeignKeys('admin_personal_access_tokens'))
            ->flatMap(fn ($foreignKey) => $foreignKey['columns'])
            ->all();

        Schema::table('admin_personal_access_tokens', function ($table) use ($constrainedColumns) {
            foreach (array_keys($this->foreignKeys()) as $column) {
                if (in_array($column, $constrainedColumns, true)) {
                    $table->dropForeign([$column]);
                }
            }
        });
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
