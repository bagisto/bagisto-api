<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;
use Webkul\BagistoApi\Metadata\DatabaseIndependentColumnTypePropertyMetadataFactory;
use Webkul\BagistoApi\Support\Sql;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class DatabaseCompatibilityTest extends BagistoApiTest
{
    /**
     * MySQL-only SQL that must go through db_grammar() instead of being written inline.
     */
    private const FORBIDDEN_PATTERNS = [
        '/,\s*\'like\'\s*,/i' => "the literal 'like' operator; use db_grammar()->caseInsensitiveLike()",
        '/\bGROUP_CONCAT\s*\(/' => 'GROUP_CONCAT; use db_grammar()->groupConcat()',
        '/\bDATEDIFF\s*\(/' => 'DATEDIFF; use db_grammar()->dateDiff()',
        '/[\'"\s(]IF\s*\(/' => 'IF(); use a CASE expression',
        '/[\'"\s(]CONCAT\s*\(/' => 'CONCAT; use db_grammar()->concat()',
        '/\bFIND_IN_SET\s*\(/' => 'FIND_IN_SET; use db_grammar()->findInSet()',
    ];

    /**
     * No source file writes MySQL-only SQL inline.
     */
    public function test_source_has_no_mysql_only_sql(): void
    {
        $violations = [];

        foreach ($this->sourceFiles() as $path) {
            $lines = file($path, FILE_IGNORE_NEW_LINES);

            foreach ($lines as $number => $line) {
                $trimmed = ltrim($line);

                if (
                    str_starts_with($trimmed, '*')
                    || str_starts_with($trimmed, '//')
                    || str_contains($line, 'description:')
                ) {
                    continue;
                }

                foreach (self::FORBIDDEN_PATTERNS as $pattern => $advice) {
                    if (preg_match($pattern, $line)) {
                        $violations[] = basename($path).':'.($number + 1).' uses '.$advice;
                    }
                }
            }
        }

        $this->assertSame([], $violations, implode(PHP_EOL, $violations));
    }

    /**
     * The full-name expression follows the connection's table prefix.
     */
    public function test_full_name_applies_the_table_prefix(): void
    {
        $prefix = DB::getTablePrefix();

        $expression = Sql::fullName('orders.customer_first_name', 'orders.customer_last_name');

        $this->assertStringContainsString($prefix.'orders.customer_first_name', $expression);
        $this->assertStringContainsString($prefix.'orders.customer_last_name', $expression);
    }

    /**
     * The LIKE clause carries the driver's own case-insensitive operator and one binding.
     */
    public function test_like_clause_uses_the_driver_operator(): void
    {
        $clause = Sql::likeClause('x');

        $this->assertSame('x '.db_grammar()->caseInsensitiveLike().' ?', $clause);
    }

    /**
     * Shipment source name resolves without MySQL's IF().
     */
    public function test_shipment_source_name_is_a_case_expression(): void
    {
        $expression = Sql::shipmentSourceName();

        $this->assertStringStartsWith('CASE WHEN', $expression);
        $this->assertStringNotContainsString('IF(', $expression);
    }

    /**
     * Column types are mapped to one API type whatever spelling MySQL, MariaDB or PostgreSQL reports.
     */
    public function test_column_types_map_to_the_same_api_type_on_every_driver(): void
    {
        $factory = (new \ReflectionClass(DatabaseIndependentColumnTypePropertyMetadataFactory::class))
            ->newInstanceWithoutConstructor();

        $sharedTypeFor = new \ReflectionMethod($factory, 'sharedTypeFor');

        $expected = [
            'int unsigned' => 'int',
            'int(10) unsigned' => 'int',
            'int(11)' => 'int',
            'integer' => 'int',
            'tinyint(1)' => 'bool',
            'boolean' => 'bool',
            'tinyint(4)' => 'string',
            'bigint unsigned' => 'string',
            'bigint(20) unsigned' => 'string',
            'smallint(6)' => 'string',
            'bigint' => 'string',
            'decimal(12,4)' => 'string',
            'numeric(12,4)' => 'string',
            'timestamp(0) without time zone' => 'string',
        ];

        $actual = [];

        foreach (array_keys($expected) as $columnType) {
            $actual[$columnType] = (string) $sharedTypeFor->invoke($factory, $columnType);
        }

        $this->assertSame($expected, $actual);
    }

    /**
     * Package source files that are checked, excluding migrations that guard on the driver themselves.
     *
     * @return array<int, string>
     */
    private function sourceFiles(): array
    {
        $files = [];

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(dirname(__DIR__, 2).'/src')
            ->exclude(['Resources', 'Database']);

        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
