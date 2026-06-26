<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\GDPR\Models\GDPRDataRequest;

/**
 * GraphQL coverage for the admin Customer GDPR Requests endpoints (Block C C4).
 */
class CustomerGdprTest extends AdminApiTestCase
{
    protected function group(): CustomerGroup
    {
        $this->seedRequiredData();

        return CustomerGroup::where('code', 'general')->first();
    }

    protected function seedCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'customer_group_id' => $this->group()->id,
            'status'            => 1,
        ], $overrides));
    }

    protected function seedRequest(array $overrides = []): GDPRDataRequest
    {
        $this->seedRequiredData();

        $customer = $overrides['customer'] ?? null;
        unset($overrides['customer']);
        if (! $customer) {
            $customer = $this->seedCustomer();
        }

        return GDPRDataRequest::create(array_merge([
            'customer_id' => $customer->id,
            'email'       => $customer->email,
            'type'        => 'delete',
            'status'      => 'pending',
            'message'     => 'Please delete my account.',
        ], $overrides));
    }

    public function test_listing_requires_auth(): void
    {
        $query = 'query { adminCustomerGdprRequests(first: 5) { edges { node { _id } } } }';
        $resp = $this->adminGraphQL($query);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_listing_returns_requests(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $query = <<<'GQL'
            query {
              adminCustomerGdprRequests(first: 50) {
                edges { node { id _id status type } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);

        $resp->assertOk();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminCustomerGdprRequests.edges') ?? []);
        expect($ids)->toContain($r->id);
    }

    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $this->seedRequest(['status' => 'pending']);
        $r = $this->seedRequest(['status' => 'processing']);

        $query = <<<'GQL'
            query($status: String) {
              adminCustomerGdprRequests(first: 50, status: $status) {
                edges { node { _id status } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['status' => 'processing'], $admin);

        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        foreach ($resp->json('data.adminCustomerGdprRequests.edges') ?? [] as $edge) {
            expect($edge['node']['status'])->toBe('processing');
        }
    }

    public function test_detail(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $query = <<<'GQL'
            query($id: ID!) { adminCustomerGdprRequest(id: $id) { id _id type status } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/customers/gdpr-requests/'.$r->id], $admin);

        $resp->assertOk();
        expect($resp->json('data.adminCustomerGdprRequest._id'))->toBe($r->id);
    }

    public function test_update_status_mutation(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest(['status' => 'pending']);

        $mutation = <<<'GQL'
            mutation($input: updateAdminCustomerGdprRequestInput!) {
              updateAdminCustomerGdprRequest(input: $input) {
                adminCustomerGdprRequest { _id status }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'id'     => '/api/admin/customers/gdpr-requests/'.$r->id,
                'status' => 'processing',
            ],
        ], $admin);

        $resp->assertOk();
        expect(GDPRDataRequest::find($r->id)->status)->toBe('processing');
    }

    public function test_delete_mutation(): void
    {
        $admin = $this->createAdmin();
        $r = $this->seedRequest();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerGdprRequestInput!) {
              deleteAdminCustomerGdprRequest(input: $input) {
                adminCustomerGdprRequest {
                  id
                  _id
                  type
                  status
                  message
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/customers/gdpr-requests/'.$r->id],
        ], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect((int) $resp->json('data.deleteAdminCustomerGdprRequest.adminCustomerGdprRequest._id'))->toBe($r->id);
        expect($resp->json('data.deleteAdminCustomerGdprRequest.adminCustomerGdprRequest.message'))->not()->toBeNull();

        expect(GDPRDataRequest::find($r->id))->toBeNull();
    }

    public function test_process_mutation_cascades_delete(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();
        $r = $this->seedRequest(['customer' => $customer, 'type' => 'delete', 'status' => 'pending']);

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerGdprProcessInput!) {
              createAdminCustomerGdprProcess(input: $input) {
                adminCustomerGdprProcess {
                  _id
                  requestId
                  customerId
                  type
                  status
                  customerDeleted
                  processedAt
                  message
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['requestId' => (string) $r->id],
        ], $admin);

        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();

        $node = $resp->json('data.createAdminCustomerGdprProcess.adminCustomerGdprProcess');
        expect($node['requestId'])->not()->toBeNull();
        expect($node['customerId'])->not()->toBeNull();
        expect($node['customerDeleted'])->not()->toBeNull();
        expect($node['processedAt'])->not()->toBeNull();

        expect(Customer::find($customer->id))->toBeNull();
        expect(GDPRDataRequest::find($r->id)?->status)->toBe('approved');
    }

    public function test_download_data_mutation(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();

        $mutation = <<<'GQL'
            mutation($input: createAdminCustomerGdprDownloadDataInput!) {
              createAdminCustomerGdprDownloadData(input: $input) {
                adminCustomerGdprDownloadData {
                  _id
                  customerId
                  customerEmail
                  generatedAt
                }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['customerId' => $customer->id],
        ], $admin);

        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();

        $node = $resp->json('data.createAdminCustomerGdprDownloadData.adminCustomerGdprDownloadData');
        expect($node['customerId'])->not()->toBeNull();
        expect($node['customerEmail'])->not()->toBeNull();
        expect($node['generatedAt'])->not()->toBeNull();
    }
}
