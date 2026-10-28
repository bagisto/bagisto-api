<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\Metadata\Get;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionPreview;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionPreviewProvider;

class AdminAppearanceSectionPreviewQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminAppearanceSectionPreviewProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminAppearanceSectionPreview
    {
        return $this->provider->provide(new Get, [], $context);
    }
}
