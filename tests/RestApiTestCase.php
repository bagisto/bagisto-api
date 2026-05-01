<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Testing\TestResponse;
use Webkul\Customer\Models\Customer;

/**
 * Base test case for REST API tests.
 *
 * Provides convenience methods for public and authenticated
 * REST requests with storefront key handling.
 */
abstract class RestApiTestCase extends BagistoApiTestCase
{
    /**
     * Execute a public GET request (storefront key only)
     */
    protected function publicGet(string $url): TestResponse
    {
        return $this->getJson($url, $this->storefrontHeaders());
    }

    /**
     * Execute a public POST request (storefront key only)
     */
    protected function publicPost(string $url, array $data = []): TestResponse
    {
        return $this->postJson($url, $data, $this->storefrontHeaders());
    }

    /**
     * Execute an authenticated GET request (storefront key + customer token)
     */
    protected function authenticatedGet(Customer $customer, string $url): TestResponse
    {
        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->getJson($url);
    }

    /**
     * Execute an authenticated POST request (storefront key + customer token)
     */
    protected function authenticatedPost(Customer $customer, string $url, array $data = []): TestResponse
    {
        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->postJson($url, $data);
    }

    /**
     * Execute an authenticated PUT request (storefront key + customer token)
     */
    protected function authenticatedPut(Customer $customer, string $url, array $data = []): TestResponse
    {
        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->putJson($url, $data);
    }

    /**
     * Execute an authenticated DELETE request (storefront key + customer token)
     */
    protected function authenticatedDelete(Customer $customer, string $url): TestResponse
    {
        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->deleteJson($url);
    }

    /**
     * Execute a public DELETE request (storefront key only)
     */
    protected function publicDelete(string $url): TestResponse
    {
        return $this->deleteJson($url, [], $this->storefrontHeaders());
    }
}
