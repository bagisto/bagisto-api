<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Testing\TestResponse;
use Webkul\Customer\Models\Customer;

/**
 * Base test case for GraphQL API tests.
 *
 * Provides convenience methods for public and authenticated
 * GraphQL requests with storefront key handling.
 */
abstract class GraphQLTestCase extends BagistoApiTestCase
{
    /** GraphQL endpoint URL */
    protected string $graphqlUrl = '/api/graphql';

    /**
     * Execute a public GraphQL query (storefront key only, no auth)
     */
    protected function graphQL(string $query, array $variables = []): TestResponse
    {
        $payload = ['query' => $query];

        if (! empty($variables)) {
            $payload['variables'] = $variables;
        }

        return $this->postJson($this->graphqlUrl, $payload, $this->storefrontHeaders());
    }

    /**
     * Execute an authenticated GraphQL query (storefront key + customer token)
     */
    protected function authenticatedGraphQL(Customer $customer, string $query, array $variables = []): TestResponse
    {
        $payload = ['query' => $query];

        if (! empty($variables)) {
            $payload['variables'] = $variables;
        }

        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->postJson($this->graphqlUrl, $payload);
    }
}
