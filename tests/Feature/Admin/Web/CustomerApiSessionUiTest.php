<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiTokenService;
use Webkul\BagistoApi\Tests\Concerns\CraftsAdminJwts;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CustomerApiSessionUiTest extends TestCase
{
    use CraftsAdminJwts;

    // ========================================================================
    // Listing
    // ========================================================================

    /**
     * Test the listing needs a signed-in admin.
     */
    public function test_index_requires_authentication(): void
    {
        $this->get(route('admin.customers.api_sessions.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    /**
     * Test the listing renders and its grid shows the customer's sessions.
     */
    public function test_index_and_datagrid_list_sessions(): void
    {
        $this->actingAdmin();

        $customer = Customer::factory()->create();
        $session = $this->issueCustomerSession($customer, ['name' => 'Kitchen tablet', 'client_type' => 'mobile']);

        $this->get(route('admin.customers.api_sessions.index'))->assertOk();

        $row = collect($this->grid()->json('records'))->firstWhere('id', $session->id);

        $this->assertNotNull($row);
        $this->assertSame('Kitchen tablet', $row['name']);
        $this->assertSame($customer->email, $row['customer_email']);
    }

    /**
     * Test the customer page shows the sessions card.
     */
    public function test_customer_page_shows_the_sessions_card(): void
    {
        $this->actingAdmin();

        $customer = Customer::factory()->create();
        $this->issueCustomerSession($customer, ['name' => 'Card phone']);

        $this->get(route('admin.customers.customers.view', $customer->id))
            ->assertOk()
            ->assertSee('Card phone');
    }

    /**
     * Test the listing and the revoke actions are closed to a role without their permission.
     */
    public function test_each_route_needs_its_permission(): void
    {
        $customer = Customer::factory()->create();
        $session = $this->issueCustomerSession($customer);

        $this->actingAdminWithPermissions(['customers']);

        $this->assertDenied($this->get(route('admin.customers.api_sessions.index')));

        $this->actingAdminWithPermissions(['customers', 'customers.api_sessions']);

        $this->grid()->assertOk();
        $this->assertDenied($this->deleteJson(route('admin.customers.api_sessions.delete', $session->id)));
        $this->assertDenied($this->postJson(route('admin.customers.api_sessions.revoke_all', $customer->id)));

        $this->assertNull($session->fresh()->revoked_at);
    }

    // ========================================================================
    // Revocation
    // ========================================================================

    /**
     * Test revoking one session leaves the customer's other sessions alone and refuses a second revoke.
     */
    public function test_revoking_one_session(): void
    {
        $this->actingAdmin();

        $customer = Customer::factory()->create();
        $lost = $this->issueCustomerSession($customer);
        $kept = $this->issueCustomerSession($customer);

        $this->deleteJson(route('admin.customers.api_sessions.delete', $lost->id))->assertOk();

        $this->assertSame(CustomerApiSession::REVOKED_BY_ADMIN, $lost->fresh()->revoked_reason);
        $this->assertNull($kept->fresh()->revoked_at);

        $this->deleteJson(route('admin.customers.api_sessions.delete', $lost->id))->assertStatus(400);
    }

    /**
     * Test revoking every session of a customer, as JSON and from the customer page form.
     */
    public function test_revoking_every_session_of_a_customer(): void
    {
        $this->actingAdmin();

        $customer = Customer::factory()->create();
        $first = $this->issueCustomerSession($customer);
        $other = $this->issueCustomerSession(Customer::factory()->create());

        $this->postJson(route('admin.customers.api_sessions.revoke_all', $customer->id))->assertOk();

        $this->assertNotNull($first->fresh()->revoked_at);
        $this->assertNull($other->fresh()->revoked_at);

        $second = $this->issueCustomerSession($customer);

        $this->from(route('admin.customers.customers.view', $customer->id))
            ->post(route('admin.customers.api_sessions.revoke_all', $customer->id))
            ->assertRedirect(route('admin.customers.customers.view', $customer->id))
            ->assertSessionHas('success');

        $this->assertNotNull($second->fresh()->revoked_at);
    }

    /**
     * Test revoking an unknown session or customer answers 404.
     */
    public function test_revoking_unknown_records_is_not_found(): void
    {
        $this->actingAdmin();

        $this->deleteJson(route('admin.customers.api_sessions.delete', 999999999))->assertNotFound();
        $this->postJson(route('admin.customers.api_sessions.revoke_all', 999999999))->assertNotFound();
    }

    /**
     * Sign access tokens with the test key pair.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureJwtKeys();
    }

    /**
     * Load the customer sessions grid data.
     */
    protected function grid(array $query = []): TestResponse
    {
        return $this->getJson(route('admin.customers.api_sessions.index', $query), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    /**
     * Start a storefront session for a customer.
     */
    protected function issueCustomerSession(Customer $customer, array $client = []): CustomerApiSession
    {
        return app(CustomerApiTokenService::class)->issue($customer, (int) core()->getCurrentChannel()->id, array_merge([
            'name' => 'UI test '.Str::random(6),
            'client_type' => CustomerApiSession::CLIENT_TYPE_OTHER,
        ], $client))->session;
    }

    /**
     * Sign in as a new all-access admin.
     */
    protected function actingAdmin(): Admin
    {
        $admin = Admin::factory()->create([
            'role_id' => Role::query()->where('permission_type', 'all')->value('id'),
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Sign in as a new admin whose role grants exactly the given permissions.
     */
    protected function actingAdminWithPermissions(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'Customer session UI '.Str::random(8),
            'description' => 'Created by the customer session screen tests.',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin = Admin::factory()->create(['role_id' => $role->id, 'status' => 1]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Assert the panel refused the request.
     */
    protected function assertDenied(TestResponse $response): void
    {
        $this->assertContains($response->getStatusCode(), [401, 403, 302], 'the request was not refused');
    }
}
