<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\Customer;

class GdprRequestTest extends GraphQLTestCase
{
    private function enableGdpr(): void
    {
        DB::table('core_config')->where('code', 'general.gdpr.settings.enabled')->delete();

        DB::table('core_config')->insert([
            'code'         => 'general.gdpr.settings.enabled',
            'value'        => '1',
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code'  => core()->getRequestedLocaleCode(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function disableGdpr(): void
    {
        DB::table('core_config')->where('code', 'general.gdpr.settings.enabled')->delete();
    }

    private function seedRequest(Customer $customer, array $overrides = []): int
    {
        return (int) DB::table('gdpr_data_request')->insertGetId(array_merge([
            'customer_id' => $customer->id,
            'email'       => $customer->email,
            'status'      => 'pending',
            'type'        => 'delete',
            'message'     => 'Please remove my data',
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
    }

    public function test_create_mutation_happy_path(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CreateGdprRequest($input: createGdprRequestInput!) {
              createGdprRequest(input: $input) {
                gdprRequest {
                  id
                  _id
                  type
                  status
                  message
                  email
                  customer {
                    _id
                  }
                  createdAt
                  successMessage
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'type'    => 'delete',
                'message' => 'Please delete my account.',
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createGdprRequest.gdprRequest');
        expect($node['type'])->toBe('delete');
        expect($node['status'])->toBe('pending');
        expect($node['message'])->toBe('Please delete my account.');
        expect($node['email'])->toBe($customer->email);
        expect((int) $node['customer']['_id'])->toBe($customer->id);

        $this->assertDatabaseHas('gdpr_data_request', [
            'customer_id' => $customer->id,
            'type'        => 'delete',
            'status'      => 'pending',
        ]);
    }

    public function test_create_mutation_invalid_type(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CreateGdprRequest($input: createGdprRequestInput!) {
              createGdprRequest(input: $input) {
                gdprRequest { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['type' => 'export', 'message' => 'msg'],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_create_mutation_missing_message(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CreateGdprRequest($input: createGdprRequestInput!) {
              createGdprRequest(input: $input) {
                gdprRequest { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['type' => 'delete', 'message' => ''],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_collection_query_returns_only_own(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();

        $this->seedRequest($customer, ['type' => 'delete']);
        $this->seedRequest($customer, ['type' => 'update']);
        $this->seedRequest($other);

        $query = <<<'GQL'
            query {
              gdprRequests {
                edges {
                  node {
                    _id
                    type
                    status
                    customer {
                      _id
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.gdprRequests.edges');
        expect(count($edges))->toBe(2);

        foreach ($edges as $edge) {
            expect((int) $edge['node']['customer']['_id'])->toBe($customer->id);
        }
    }

    public function test_item_query_returns_own(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $query = <<<'GQL'
            query GetGdprRequest($id: ID!) {
              gdprRequest(id: $id) {
                _id
                type
                status
                message
                email
                customer {
                  _id
                }
                createdAt
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query, [
            'id' => '/api/shop/gdpr-requests/'.$id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.gdprRequest');
        expect((int) $node['_id'])->toBe($id);
        expect($node['type'])->toBe('delete');
        expect($node['status'])->toBe('pending');
        expect((int) $node['customer']['_id'])->toBe($customer->id);
    }

    public function test_item_query_cross_customer_errors(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $id = $this->seedRequest($other);

        $query = <<<'GQL'
            query GetGdprRequest($id: ID!) {
              gdprRequest(id: $id) {
                _id
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query, [
            'id' => '/api/shop/gdpr-requests/'.$id,
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_revoke_mutation_happy_path(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer, ['status' => 'pending']);

        $mutation = <<<'GQL'
            mutation RevokeGdprRequest($input: revokeGdprRequestInput!) {
              revokeGdprRequest(input: $input) {
                gdprRequest {
                  _id
                  status
                  revokedAt
                  successMessage
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/gdpr-requests/'.$id],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.revokeGdprRequest.gdprRequest');
        expect($node['status'])->toBe('revoked');
        expect($node['revokedAt'])->not->toBeNull();

        $this->assertDatabaseHas('gdpr_data_request', [
            'id'     => $id,
            'status' => 'revoked',
        ]);
    }

    public function test_revoke_mutation_not_allowed_when_approved(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer, ['status' => 'approved']);

        $mutation = <<<'GQL'
            mutation RevokeGdprRequest($input: revokeGdprRequestInput!) {
              revokeGdprRequest(input: $input) {
                gdprRequest { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/gdpr-requests/'.$id],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_revoke_mutation_cross_customer_errors(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $id = $this->seedRequest($other);

        $mutation = <<<'GQL'
            mutation RevokeGdprRequest($input: revokeGdprRequestInput!) {
              revokeGdprRequest(input: $input) {
                gdprRequest { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/gdpr-requests/'.$id],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_delete_mutation_happy_path(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $id = $this->seedRequest($customer);

        $mutation = <<<'GQL'
            mutation DeleteGdprRequest($input: deleteGdprRequestInput!) {
              deleteGdprRequest(input: $input) {
                gdprRequest {
                  _id
                  successMessage
                }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/gdpr-requests/'.$id],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $this->assertDatabaseMissing('gdpr_data_request', ['id' => $id]);
    }

    public function test_delete_mutation_cross_customer_errors(): void
    {
        $this->enableGdpr();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();
        $id = $this->seedRequest($other);

        $mutation = <<<'GQL'
            mutation DeleteGdprRequest($input: deleteGdprRequestInput!) {
              deleteGdprRequest(input: $input) {
                gdprRequest { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['id' => '/api/shop/gdpr-requests/'.$id],
        ]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('gdpr_data_request', ['id' => $id]);
    }

    public function test_collection_query_requires_authentication(): void
    {
        $this->enableGdpr();

        $query = <<<'GQL'
            query {
              gdprRequests {
                edges { node { _id } }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_create_when_disabled_returns_disabled_error(): void
    {
        $this->disableGdpr();
        $customer = $this->createCustomer();

        $mutation = <<<'GQL'
            mutation CreateGdprRequest($input: createGdprRequestInput!) {
              createGdprRequest(input: $input) {
                gdprRequest { _id }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['type' => 'delete', 'message' => 'msg'],
        ]);

        expect($response->json('errors'))->not->toBeNull();
        expect(strtolower(json_encode($response->json('errors'))))->toContain('disabled');
    }

    public function test_list_when_disabled_returns_disabled_error(): void
    {
        $this->disableGdpr();
        $customer = $this->createCustomer();

        $query = <<<'GQL'
            query {
              gdprRequests {
                edges { node { _id } }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query);

        expect($response->json('errors'))->not->toBeNull();
        expect(strtolower(json_encode($response->json('errors'))))->toContain('disabled');
    }
}
