<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Webkul\BagistoApi\State\ProductRelationFlagResolver;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class ProductRelationFlagResolverScopeTest extends BagistoApiTest
{
    public function test_resolver_is_scoped_to_the_request_lifecycle(): void
    {
        $first = app(ProductRelationFlagResolver::class);

        expect(app(ProductRelationFlagResolver::class))->toBe($first);

        $this->app->forgetScopedInstances();

        expect(app(ProductRelationFlagResolver::class))->not->toBe($first);
    }
}
