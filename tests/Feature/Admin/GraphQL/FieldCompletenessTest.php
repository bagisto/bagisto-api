<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

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

    /**
     * The type fields cache.
     */
    private array $typeFieldsCache = [];

    /**
     * Test every admin read query resolves all fields without errors.
     */
    public function test_every_admin_read_query_resolves_all_fields_without_errors(): void
    {
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

        foreach ($qfields as $f) {
            $ret = $this->baseType($f['type']);
            if (! $this->isAdmin($f['name'], $ret['name'])) {
                continue;
            }
            if (
                ! $ret['name']
                || ! str_ends_with($ret['name'], 'Connection')
            ) {
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

            if (
                $node
                && ! isset($idByNodeType[$nodeType])
            ) {
                $idByNodeType[$nodeType] = $node['id'] ?? null;
                $collFieldByNodeType[$nodeType] = $f['name'];
            }
            if ($errors) {
                $failures[] = "{$f['name']} (list): ".($errors[0]['message'] ?? '?').' @ '.json_encode($errors[0]['path'] ?? []);
            }
            $checked++;
        }

        foreach ($qfields as $f) {
            $ret = $this->baseType($f['type']);
            if (! $this->isAdmin($f['name'], $ret['name'])) {
                continue;
            }
            if (
                $ret['name']
                && str_ends_with($ret['name'], 'Connection')
            ) {
                continue;
            }
            if (in_array($f['name'], self::SKIP_FIELDS, true)) {
                continue;
            }
            $argNames = array_map(fn ($a) => $a['name'], $f['args'] ?? []);
            if (! in_array('id', $argNames, true)) {
                continue;
            }
            if ($this->hasUnsatisfiableArgs($f, ['id'])) {
                continue;
            }

            $iri = $idByNodeType[$ret['name']] ?? null;
            if (! $iri) {
                continue;
            }

            $sel = $this->selection($admin, $ret['name'], 1);
            $query = "query(\$id: ID!) { {$f['name']}(id: \$id) { id _id\n$sel } }";

            $errors = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $useIri = $iri;
                if (
                    $attempt > 0
                    && isset($collFieldByNodeType[$ret['name']])
                ) {
                    $useIri = $this->freshId($admin, $collFieldByNodeType[$ret['name']]);
                    if (! $useIri) {
                        $errors = null;
                        break;
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

    /**
     * Introspect query fields.
     */
    private function introspectQueryFields(object $admin): array
    {
        $q = 'query{ __schema { queryType { fields { name args { name type { kind name ofType { kind name } } } type { kind name ofType { kind name ofType { kind name ofType { kind name } } } } } } } }';

        return $this->adminGraphQL($q, [], $admin)->json('data.__schema.queryType.fields') ?? [];
    }

    /**
     * Type fields.
     */
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

    /**
     * Base type.
     */
    private function baseType(array $t): array
    {
        while (
            in_array($t['kind'] ?? '', ['NON_NULL', 'LIST'], true)
            && isset($t['ofType'])
        ) {
            $t = $t['ofType'];
        }

        return [
            'kind' => $t['kind'] ?? null,
            'name' => $t['name'] ?? null,
        ];
    }

    /**
     * Whether admin.
     */
    private function isAdmin(string $field, ?string $typeName): bool
    {
        return str_starts_with((string) $typeName, 'Admin') || str_contains(strtolower($field), 'admin');
    }

    /** True if the field has a required (NON_NULL) arg not in the allowed set. */
    private function hasUnsatisfiableArgs(array $f, array $allowed): bool
    {
        foreach ($f['args'] ?? [] as $a) {
            if (
                ($a['type']['kind'] ?? null) === 'NON_NULL'
                && ! in_array($a['name'], $allowed, true)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Connection node type.
     */
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

    /**
     * Selection.
     */
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
            } elseif (
                $b['kind'] === 'OBJECT'
                && $depth > 0
                && $b['name']
            ) {
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
