<?php

namespace Webkul\BagistoApi\Support;

use Illuminate\Support\Facades\DB;

/**
 * Database-portable SQL fragments for raw expressions, built on core's db_grammar().
 */
class Sql
{
    /**
     * Prefix a table-qualified column with the connection's table prefix.
     */
    public static function column(string $column): string
    {
        return DB::getTablePrefix().$column;
    }

    /**
     * Join a first and a last name column with a single space.
     */
    public static function fullName(string $firstColumn, string $lastColumn): string
    {
        return db_grammar()->concat(
            self::column($firstColumn),
            "' '",
            self::column($lastColumn),
        );
    }

    /**
     * Turn an expression into a case-insensitive LIKE condition with one binding.
     */
    public static function likeClause(string $expression): string
    {
        return $expression.' '.db_grammar()->caseInsensitiveLike().' ?';
    }

    /**
     * Resolve a shipment's inventory source name, preferring the live source over the stored snapshot.
     */
    public static function shipmentSourceName(): string
    {
        $sourceId = self::column('shipments.inventory_source_id');
        $liveName = self::column('inventory_sources.name');
        $snapshotName = self::column('shipments.inventory_source_name');

        return "CASE WHEN {$sourceId} IS NOT NULL THEN {$liveName} ELSE {$snapshotName} END";
    }

    /**
     * Whole days elapsed between a column and now.
     */
    public static function daysSince(string $column): string
    {
        return db_grammar()->dateDiff(db_grammar()->now(), self::column($column));
    }

    /**
     * Distinct values of a column joined into one string.
     */
    public static function distinctList(string $column, string $separator = ','): string
    {
        return db_grammar()->groupConcat(self::column($column), $separator, true);
    }
}
