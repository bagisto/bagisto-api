<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportImages;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportImagesProcessor;

class AdminSettingsDataTransferImportImagesResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminSettingsDataTransferImportImagesProcessor $processor) {}

    public function __invoke(?object $item, array $context): AdminSettingsDataTransferImportImages
    {
        return $this->processor->status((int) ($context['args']['importId'] ?? 0));
    }
}
