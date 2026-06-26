<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * Durable field-completeness guard for every admin GraphQL READ query.
 *
 * Introspects the schema, then for each admin* list + detail query builds a
 * selection of EVERY scalar field (plus one level into to-one objects and
 * connection nodes) and asserts the query returns no `errors`. This is the
 * automated form of the read-only sweep that surfaced the listing/detail
 * non-null-field 500s — it catches that whole class so a future provider that
 * forgets to populate a non-null column (or null-sets a non-null to-one)
 * fails here instead of silently 500ing in production.
 *
 * Queries only — no mutations, no writes. Resources with no row in the test DB
 * are skipped (nothing to resolve against). Parent-scoped sub-resources and
 * custom single-payload queries (stats / reporting / configuration) need args
 * this generic harness can't synthesise and are skipped by design.
 */
class FieldCompletenessTest extends AdminApiTestCase
{
    /** Queries whose required args this generic harness cannot supply. */
    private const SKIP_FIELDS = [
        // parent-scoped sub-resources (need productId / cartRuleId / orderId / customerId / cartId)
        'adminCatalogProductInventories', 'adminCatalogProductCustomerGroupPrices',
        'adminMarketingCartRuleCoupons', 'adminOrderComments',
        'adminCustomerAddresses', 'adminCustomerNotes', 'adminCustomerCartItems',
        'adminCustomerWishlistItems', 'adminCustomerCompareItems', 'adminCustomerRecentOrderItems',
        'adminCartShippingRates', 'adminCartPaymentMethods',
        // custom single-payload (need type / entity / slug, or have no _id)
        'statsAdminDashboard', 'statsAdminReportingOverview', 'statsAdminReportingSales',
        'statsAdminReportingCustomers', 'statsAdminReportingProducts',
        'viewStatsAdminReportingSales', 'viewStatsAdminReportingCustomers', 'viewStatsAdminReportingProducts',
        'menuAdminConfigurationMenu', 'listAdminConfigurationSlug', 'valuesAdminConfigurationValues',
        'getAdminMenu', 'getAdminPermissions', 'readAdminProfile',
    ];

    public function test_every_admin_read_query_resolves_all_fields_without_errors(): void
    {
        // This is a whole-schema READ sweep: it queries every admin list/detail
        // against ambient rows. Run concurrently with the mutating suite, those rows
        // are created/deleted underneath it (the AUDIT-*/GQ-* fixtures), so it flakes.
        // It's a deterministic guard when run on its own — skip it inside paratest
        // workers and invoke it serially:
        //   php artisan test packages/Webkul/BagistoApi/tests/Feature/Admin/GraphQL/FieldCompletenessTest.php
        if (getenv('LARAVEL_PARALLEL_TESTING')) {
            $this->markTestSkipped('Field-completeness sweep runs serially (reads ambient data; incompatible with parallel mutation).');
        }

        $admin = $this->createAdmin();

        $qfields = $this->introspectQueryFields($admin);
        $this->assertNotEmpty($qfields, 'schema introspection returned no query fields');

        $idByNodeType = [];
        $collFieldByNodeType = [];
        $failures = [];
        $checked = 0;

        // ---- collections first (also harvest ids for the detail pass) ----
        foreach ($qfields as $f) {
            $ret = $this->baseType($f['type']);
            if (! $this->isAdmin($f['name'], $ret['name'])) {
                continue;
            }
            if (! $ret['name'] || ! str_ends_with($ret['name'], 'Connection')) {
                continue;
            }
            if (in_array($f['name'], self::SKIP_FIELDS, true)) {
                continue;
            }
            if ($this->hasUnsatisfiableArgs($f, ['first', 'after', 'last', 'before'])) {
                continue;
            }

            $nodeType = $this->connectionNodeType($admin, $ret['name']);
            if (! $nodeType) {
                continue;
            }

            $sel = $this->selection($admin, $nodeType, 1);
            $query = "query { {$f['name']}(first: 1) { edges { node { id _id\n$sel } } } }";

            // Retry on error: under --parallel, concurrent tests mutate the rows this
            // sweep reads, so a transient error clears on a fresh attempt. A real
            // field-resolution bug is deterministic and survives every attempt.
            $errors = null;
            $node = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $resp = $this->adminGraphQL($query, [], $admin);
                $errors = $resp->json('errors');
                $node = $resp->json("data.{$f['name']}.edges.0.node");
                if (! $errors) {
                    break;
                }
                usleep(50000);
            }

            if ($node && ! isset($idByNodeType[$nodeType])) {
                $idByNodeType[$nodeType] = $node['id'] ?? null;
                $collFieldByNodeType[$nodeType] = $f['name'];
            }
            if ($errors) {
                $failures[] = "{$f['name']} (list): ".($errors[0]['message'] ?? '?').' @ '.json_encode($errors[0]['path'] ?? []);
            }
            $checked++;
        }

        // ---- detail/item queries ----
        foreach ($qfields as $f) {
            $ret = $this->baseType($f['type']);
            if (! $this->isAdmin($f['name'], $ret['name'])) {
                continue;
            }
            if ($ret['name'] && str_ends_with($ret['name'], 'Connection')) {
                continue;
            }
            if (in_array($f['name'], self::SKIP_FIELDS, true)) {
                continue;
            }
            $argNames = array_map(fn ($a) => $a['name'], $f['args'] ?? []);
            if (! in_array('id', $argNames, true)) {
                continue; // not a standard id-keyed detail query
            }
            if ($this->hasUnsatisfiableArgs($f, ['id'])) {
                continue;
            }

            $iri = $idByNodeType[$ret['name']] ?? null;
            if (! $iri) {
                continue; // no row harvested for this type → nothing to test
            }

            $sel = $this->selection($admin, $ret['name'], 1);
            $query = "query(\$id: ID!) { {$f['name']}(id: \$id) { id _id\n$sel } }";

            // Retry with a freshly-harvested id: under --parallel the harvested row
            // can be deleted/mutated mid-sweep (e.g. the AUDIT-*/GQ-* products), so a
            // stale id 500s transiently. A real field bug survives every attempt.
            $errors = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $useIri = $iri;
                if ($attempt > 0 && isset($collFieldByNodeType[$ret['name']])) {
                    $useIri = $this->freshId($admin, $collFieldByNodeType[$ret['name']]);
                    if (! $useIri) {
                        $errors = null;
                        break; // no row left to test → not a failure
                    }
                }
                $resp = $this->adminGraphQL($query, ['id' => $useIri], $admin);
                $errors = $resp->json('errors');
                if (! $errors) {
                    break;
                }
                usleep(50000);
            }
            if ($errors) {
                $failures[] = "{$f['name']} (detail): ".($errors[0]['message'] ?? '?').' @ '.json_encode($errors[0]['path'] ?? []);
            }
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'no admin read queries were exercised');
        $this->assertSame([], $failures, "Admin GraphQL field-resolution failures:\n".implode("\n", $failures));
    }

    // ---------- helpers ----------

    private function introspectQueryFields(object $admin): array
    {
        $q = 'query{ __schema { queryType { fields { name args { name type { kind name ofType { kind name } } } type { kind name ofType { kind name ofType { kind name ofType { kind name } } } } } } } }';

        return $this->adminGraphQL($q, [], $admin)->json('data.__schema.queryType.fields') ?? [];
    }

    private array $typeFieldsCache = [];

    private function typeFields(object $admin, string $name): array
    {
        if (isset($this->typeFieldsCache[$name])) {
            return $this->typeFieldsCache[$name];
        }
        $q = 'query($n:String!){ __type(name:$n){ fields { name type { kind name ofType { kind name ofType { kind name ofType { kind name ofType { kind name } } } } } } } }';

        return $this->typeFieldsCache[$name] = $this->adminGraphQL($q, ['n' => $name], $admin)->json('data.__type.fields') ?? [];
    }

    /** Re-harvest the current first-row IRI for a collection field (race recovery). */
    private function freshId(object $admin, string $collectionField): ?string
    {
        $resp = $this->adminGraphQL("query { {$collectionField}(first: 1) { edges { node { id } } } }", [], $admin);

        return $resp->json("data.{$collectionField}.edges.0.node.id");
    }

    private function baseType(array $t): array
    {
        while (in_array($t['kind'] ?? '', ['NON_NULL', 'LIST'], true) && isset($t['ofType'])) {
            $t = $t['ofType'];
        }

        return ['kind' => $t['kind'] ?? null, 'name' => $t['name'] ?? null];
    }

    private function isAdmin(string $field, ?string $typeName): bool
    {
        return str_starts_with((string) $typeName, 'Admin') || str_contains(strtolower($field), 'admin');
    }

    /** True if the field has a required (NON_NULL) arg not in the allowed set. */
    private function hasUnsatisfiableArgs(array $f, array $allowed): bool
    {
        foreach ($f['args'] ?? [] as $a) {
            if (($a['type']['kind'] ?? null) === 'NON_NULL' && ! in_array($a['name'], $allowed, true)) {
                return true;
            }
        }

        return false;
    }

    private function connectionNodeType(object $admin, string $connName): ?string
    {
        foreach ($this->typeFields($admin, $connName) as $f) {
            if ($f['name'] === 'edges') {
                $edgeType = $this->baseType($f['type'])['name'];
                foreach ($this->typeFields($admin, $edgeType) as $g) {
                    if ($g['name'] === 'node') {
                        return $this->baseType($g['type'])['name'];
                    }
                }
            }
        }

        return null;
    }

    private function selection(object $admin, string $typeName, int $depth): string
    {
        $parts = [];
        foreach ($this->typeFields($admin, $typeName) as $f) {
            $fn = $f['name'];
            if (str_starts_with($fn, '__')) {
                continue;
            }
            $b = $this->baseType($f['type']);
            if (in_array($b['kind'], ['SCALAR', 'ENUM'], true)) {
                $parts[] = $fn;
            } elseif ($b['kind'] === 'OBJECT' && $depth > 0 && $b['name']) {
                if (str_ends_with($b['name'], 'Connection')) {
                    $node = $this->connectionNodeType($admin, $b['name']);
                    if ($node) {
                        $inner = $this->selection($admin, $node, 0);
                        if ($inner !== '') {
                            $parts[] = "$fn { edges { node { $inner } } }";
                        }
                    }
                } else {
                    $inner = $this->selection($admin, $b['name'], 0);
                    if ($inner !== '') {
                        $parts[] = "$fn { $inner }";
                    }
                }
            }
        }

        return implode("\n", $parts);
    }
}
