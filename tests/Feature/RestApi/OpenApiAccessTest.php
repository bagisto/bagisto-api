<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Route;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class OpenApiAccessTest extends RestApiTestCase
{
    /**
     * The methods an OpenAPI path item documents an operation for.
     */
    private const METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * The admin document declares the access token and API token schemes and no other.
     */
    public function test_admin_document_declares_its_bearer_schemes(): void
    {
        $schemes = $this->document('admin')['components']['securitySchemes'];

        expect(array_keys($schemes))->toBe(['adminAccessToken', 'adminApiToken']);

        foreach ($schemes as $scheme) {
            expect($scheme['type'])->toBe('http')
                ->and($scheme['scheme'])->toBe('bearer')
                ->and($scheme['bearerFormat'])->toBe('JWT')
                ->and($scheme['description'])->not->toBe('');
        }
    }

    /**
     * The storefront document declares the storefront key, customer token and guest cart token schemes.
     */
    public function test_shop_document_declares_its_schemes(): void
    {
        $schemes = $this->document('shop')['components']['securitySchemes'];

        expect(array_keys($schemes))->toBe(['storefrontKey', 'customerToken', 'guestCartToken'])
            ->and($schemes['storefrontKey']['type'])->toBe('apiKey')
            ->and($schemes['storefrontKey']['in'])->toBe('header')
            ->and($schemes['storefrontKey']['name'])->toBe('X-STOREFRONT-KEY');
    }

    /**
     * Both documents open with a guide to the authentication flow.
     */
    public function test_documents_describe_the_authentication_flow(): void
    {
        expect($this->document('admin')['info']['description'])
            ->toContain('access_token_expired')
            ->toContain('refresh_token_invalid')
            ->toContain('/auth/refresh');

        expect($this->document('shop')['info']['description'])
            ->toContain('X-STOREFRONT-KEY')
            ->toContain('/cart-tokens');
    }

    /**
     * The sign-in operations need no token, the session operations an access token, the rest either token.
     */
    public function test_admin_operations_state_the_tokens_they_accept(): void
    {
        $paths = $this->document('admin')['paths'];

        foreach (['/auth/login', '/auth/two-factor', '/auth/refresh', '/auth/forgot-password', '/auth/reset-password'] as $path) {
            expect($paths[$path]['post']['security'])->toBe([], "{$path} must be public");
        }

        expect($paths['/auth/logout']['post']['security'])->toBe([['adminAccessToken' => []]])
            ->and($paths['/account']['put']['security'])->toBe([['adminAccessToken' => []]])
            ->and($paths['/get']['get']['security'])->toBe([['adminAccessToken' => []], ['adminApiToken' => []]]);
    }

    /**
     * Customer operations need the customer token and cart operations take a guest cart token instead.
     */
    public function test_shop_operations_state_the_tokens_they_accept(): void
    {
        $paths = $this->document('shop')['paths'];

        expect($paths['/products']['get']['security'])->toBe([['storefrontKey' => []]])
            ->and($paths['/customer-orders']['get']['security'])->toBe([['storefrontKey' => [], 'customerToken' => []]])
            ->and($paths['/add-product-in-cart']['post']['security'])->toBe([
                ['storefrontKey' => [], 'customerToken' => []],
                ['storefrontKey' => [], 'guestCartToken' => []],
            ]);
    }

    /**
     * Every operation behind a token documents the 401 it answers a missing token with.
     */
    public function test_every_protected_operation_documents_its_401(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            foreach ($this->document($surface)['paths'] as $path => $operations) {
                foreach ($operations as $method => $operation) {
                    if (
                        ! is_array($operation)
                        || ($operation['security'] ?? null) === []
                    ) {
                        continue;
                    }

                    expect($operation['responses'])->toHaveKey('401', message: "{$surface} {$method} {$path} documents no 401");
                }
            }
        }
    }

    /**
     * Every package route under the shop and admin prefixes is in its OpenAPI document, plain Laravel routes included.
     */
    public function test_every_package_route_is_documented(): void
    {
        foreach (['shop', 'admin'] as $surface) {
            $documented = [];

            foreach ($this->document($surface)['paths'] as $path => $operations) {
                foreach (array_keys($operations) as $method) {
                    $documented[] = strtoupper($method).' '.$path;
                }
            }

            $missing = array_values(array_diff($this->packageRoutes($surface), $documented));

            expect($missing)->toBe([], "openapi-{$surface} misses package routes");
        }
    }

    /**
     * The OpenAPI document of a surface, as its docs endpoint serves it.
     */
    private function document(string $surface): array
    {
        return $this->get("/api/{$surface}/docs")->assertOk()->json();
    }

    /**
     * The "METHOD path" of every API Platform operation and package controller route of a surface.
     *
     * @return list<string>
     */
    private function packageRoutes(string $surface): array
    {
        $prefix = "api/{$surface}/";

        $routes = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $action = (string) $route->getActionName();

            if (
                ! str_starts_with($route->uri(), $prefix)
                || ! $this->isDocumentedAction($action)
            ) {
                continue;
            }

            $path = '/'.str_replace('{_format?}', '', substr($route->uri(), strlen($prefix)));

            foreach (array_intersect($route->methods(), self::METHODS) as $method) {
                $routes[] = $method.' '.$path;
            }
        }

        return $routes;
    }

    /**
     * Whether a route action is an exposed API Platform operation or a package controller other than the docs and GraphQL ones.
     */
    private function isDocumentedAction(string $action): bool
    {
        if (str_starts_with($action, 'ApiPlatform\\Laravel\\Controller\\ApiPlatformController')) {
            return true;
        }

        return str_starts_with($action, 'Webkul\\BagistoApi\\Http\\Controllers\\')
            && ! str_contains($action, 'SwaggerUI')
            && ! str_contains($action, 'GraphQL');
    }
}
