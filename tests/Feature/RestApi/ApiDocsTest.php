<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;

/**
 * Smoke coverage for the API documentation and playground pages.
 */
class ApiDocsTest extends RestApiTestCase
{
    /**
     * The API Platform documentation page requested by a browser is served as HTML.
     */
    public function test_api_docs_page_renders_as_html(): void
    {
        $response = $this->get('/api/docs', ['Accept' => 'text/html']);

        $response->assertOk();
        expect($response->getContent())->toContain('<!DOCTYPE html>');
        expect($response->headers->get('Content-Type'))->toContain('text/html');
    }

    /**
     * The shop OpenAPI document is served as JSON.
     */
    public function test_shop_docs_spec_is_json(): void
    {
        $response = $this->get('/api/shop/docs');

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('application/json');
        expect($response->json())->toHaveKey('paths');
    }

    /**
     * The admin OpenAPI document is served as JSON.
     */
    public function test_admin_docs_spec_is_json(): void
    {
        $response = $this->get('/api/admin/docs');

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('application/json');
        expect($response->json())->toHaveKey('paths');
    }

    /**
     * The admin GraphQL playground renders as HTML.
     */
    public function test_admin_graphiql_page_renders(): void
    {
        $response = $this->get('/api/admin/graphiql', ['Accept' => 'text/html']);

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/html');
    }
}
