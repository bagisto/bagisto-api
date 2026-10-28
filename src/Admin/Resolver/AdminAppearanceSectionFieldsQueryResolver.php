<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Metadata\Get;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionFields;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionFieldsProvider;

class AdminAppearanceSectionFieldsQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminAppearanceSectionFieldsProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminAppearanceSectionFields
    {
        return $this->provider->provide(new Get, [], $context);
    }
}
