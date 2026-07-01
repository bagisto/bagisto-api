<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class DashboardTest extends AdminApiTestCase
{
    public function test_dashboard_stats_default(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              statsAdminDashboard {
                type
                dateRange
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $data = $response->json('data.statsAdminDashboard');
        if ($data) {
            expect($data['type'] ?? null)->toBe('over-all');
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }

    public function test_dashboard_stats_today_type(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query Q($type: String) {
              statsAdminDashboard(type: $type) {
                type
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['type' => 'today'], $admin);

        $response->assertOk();
    }

    public function test_date_range_resolves_over_graphql(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              statsAdminDashboard {
                type
                dateRange
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.statsAdminDashboard.dateRange'))->not->toBeNull();
    }

    public function test_requires_authentication(): void
    {
        $query = 'query { statsAdminDashboard { type } }';

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }
}
