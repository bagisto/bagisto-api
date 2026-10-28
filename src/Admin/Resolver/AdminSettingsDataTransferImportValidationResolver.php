<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportValidation;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportValidationProcessor;

class AdminSettingsDataTransferImportValidationResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminSettingsDataTransferImportValidationProcessor $processor) {}

    public function __invoke(?object $item, array $context): AdminSettingsDataTransferImportValidation
    {
        return $this->processor->status((int) ($context['args']['importId'] ?? 0));
    }
}
