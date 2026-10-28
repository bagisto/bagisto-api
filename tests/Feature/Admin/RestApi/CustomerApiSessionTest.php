<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class CustomerApiSessionTest extends AdminApiTestCase
{
    /**
     * The customer's password in the audit tests.
     */
    protected const PASSWORD = 'Password123!';

    // ========================================================================
    // Listing
    // ========================================================================

    /**
     * Test the listing returns every live customer session in the paginated envelope.
     */
    public function test_listing_returns_live_sessions(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $live = $this->customerTokenPair($customer)->session;
        $ended = $this->customerTokenPair($customer)->session;

        $ended->forceFill(['revoked_at' => now(), 'revoked_reason' => CustomerApiSession::REVOKED_LOGOUT])->save();

        $response = $this->adminGet($admin, '/api/admin/customers/api-sessions?per_page=50&customer_id='.$customer->id)->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertNotContains($ended->id, $ids);
        $this->assertNotNull($response->json('meta.total'));

        $row = collect($response->json('data'))->firstWhere('id', $live->id);

        $this->assertSame($customer->id, $row['customerId']);
        $this->assertSame($customer->email, $row['customerEmail']);
    }

    /**
     * Test the per-customer listing returns only that customer's sessions.
     */
    public function test_per_customer_listing(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();

        $mine = $this->customerTokenPair($customer)->session;
        $theirs = $this->customerTokenPair($other)->session;

        $ids = collect($this->adminGet($admin, "/api/admin/customers/{$customer->id}/api-sessions")->assertOk()->json('data'))->pluck('id')->all();

        $this->assertContains($mine->id, $ids);
        $this->assertNotContains($theirs->id, $ids);

        $this->adminGet($admin, '/api/admin/customers/999999999/api-sessions')->assertNotFound();
    }

    /**
     * Test the listing needs customers.api_sessions.
     */
    public function test_listing_needs_its_permission(): void
    {
        $limited = $this->createAdminWithRolePermissions(['customers']);

        $this->adminGet($limited, '/api/admin/customers/api-sessions')->assertForbidden();

        $allowed = $this->createAdminWithRolePermissions(['customers', 'customers.api_sessions']);

        $this->adminGet($allowed, '/api/admin/customers/api-sessions')->assertOk();
    }

    // ========================================================================
    // Revocation
    // ========================================================================

    /**
     * Test revoking one session stops its access token and records the admin in the audit trail.
     */
    public function test_revoking_one_session(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $lost = $this->customerTokenPair($customer);
        $kept = $this->customerTokenPair($customer);

        $this->deleteJson('/api/admin/customers/api-sessions/'.$lost->session->id, [], $this->adminHeaders($admin))->assertNoContent();

        $this->assertSame(CustomerApiSession::REVOKED_BY_ADMIN, $lost->session->fresh()->revoked_reason);
        $this->assertNull($kept->session->fresh()->revoked_at);

        $this->getJson('/api/shop/customer-profile', array_merge($this->storefrontHeaders(), ['Authorization' => 'Bearer '.$lost->accessToken]))
            ->assertStatus(401);

        $audit = AdminApiAudit::query()
            ->where('event', AdminApiAuthAuditor::EVENT_SESSION_REVOKED)
            ->where('token_id', $lost->session->id)
            ->where('token_kind', AdminApiAudit::TOKEN_KIND_CUSTOMER_SESSION)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($admin->name, $audit->admin_name);
        $this->assertSame($admin->id, $audit->new_values['admin_id'] ?? null);

        $this->deleteJson('/api/admin/customers/api-sessions/'.$lost->session->id, [], $this->adminHeaders($admin))->assertNotFound();
    }

    /**
     * Test revoking every session of a customer leaves other customers alone.
     */
    public function test_revoking_every_session_of_a_customer(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $first = $this->customerTokenPair($customer)->session;
        $second = $this->customerTokenPair($customer)->session;
        $other = $this->customerTokenPair($this->createCustomer())->session;

        $this->adminPost($admin, "/api/admin/customers/{$customer->id}/revoke-api-sessions")
            ->assertSuccessful()
            ->assertJsonPath('revoked', 2);

        $this->assertNotNull($first->fresh()->revoked_at);
        $this->assertNotNull($second->fresh()->revoked_at);
        $this->assertNull($other->fresh()->revoked_at);

        $this->adminPost($admin, '/api/admin/customers/999999999/revoke-api-sessions')->assertNotFound();
    }

    /**
     * Test revoking needs customers.api_sessions.delete.
     */
    public function test_revoking_needs_the_delete_permission(): void
    {
        $customer = $this->createCustomer();
        $session = $this->customerTokenPair($customer)->session;

        $viewer = $this->createAdminWithRolePermissions(['customers', 'customers.api_sessions']);

        $this->deleteJson('/api/admin/customers/api-sessions/'.$session->id, [], $this->adminHeaders($viewer))->assertForbidden();
        $this->adminPost($viewer, "/api/admin/customers/{$customer->id}/revoke-api-sessions")->assertForbidden();

        $this->assertNull($session->fresh()->revoked_at);
    }

    // ========================================================================
    // Audit
    // ========================================================================

    /**
     * Test storefront login, failed login, refresh, reuse and logout land in the audit trail.
     */
    public function test_customer_authentication_is_audited(): void
    {
        $customer = $this->createCustomer([
            'email' => 'audit-'.uniqid().'@example.com',
            'password' => Hash::make(self::PASSWORD),
            'status' => 1,
            'is_verified' => 1,
        ]);

        $this->postJson('/api/shop/customer/login', ['email' => $customer->email, 'password' => 'Wrong-password-1'], $this->storefrontHeaders());

        $login = $this->postJson('/api/shop/customer/login', ['email' => $customer->email, 'password' => self::PASSWORD], $this->storefrontHeaders());

        $refreshToken = $login->json('refreshToken');

        $refreshed = $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $refreshToken], $this->storefrontHeaders());

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $refreshToken], $this->storefrontHeaders());

        $next = $this->customerTokenPair($customer);

        $this->postJson('/api/shop/customer/logout', [], array_merge($this->storefrontHeaders(), ['Authorization' => 'Bearer '.$next->accessToken]));

        $events = AdminApiAudit::query()
            ->where('token_kind', AdminApiAudit::TOKEN_KIND_CUSTOMER_SESSION)
            ->where('user_id', $customer->id)
            ->pluck('event')
            ->all();

        $this->assertNotNull($refreshed->json('accessToken'));

        foreach ([
            AdminApiAuthAuditor::EVENT_LOGIN_FAILED,
            AdminApiAuthAuditor::EVENT_LOGIN,
            AdminApiAuthAuditor::EVENT_REFRESH,
            AdminApiAuthAuditor::EVENT_REFRESH_REUSE,
            AdminApiAuthAuditor::EVENT_LOGOUT,
        ] as $event) {
            $this->assertContains($event, $events, "no {$event} audit row");
        }
    }
}
