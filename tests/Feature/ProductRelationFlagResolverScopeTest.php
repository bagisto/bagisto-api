<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Webkul\BagistoApi\State\ProductRelationFlagResolver;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class ProductRelationFlagResolverScopeTest extends BagistoApiTest
{
    /**
     * Test resolver is scoped to the request lifecycle.
     */
    public function test_resolver_is_scoped_to_the_request_lifecycle(): void
    {
        $first = app(ProductRelationFlagResolver::class);

        expect(app(ProductRelationFlagResolver::class))->toBe($first);

        $this->app->forgetScopedInstances();

        expect(app(ProductRelationFlagResolver::class))->not->toBe($first);
    }
}
