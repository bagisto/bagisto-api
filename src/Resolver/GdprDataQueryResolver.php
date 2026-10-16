<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\GdprDataOutput;
use Webkul\BagistoApi\State\GdprDataProvider;

class GdprDataQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected GdprDataProvider $provider) {}

    /**
     * Resolve the authenticated customer's GDPR export.
     */
    public function __invoke(?object $item, array $context): GdprDataOutput
    {
        return $this->provider->provideCurrentCustomer();
    }
}
