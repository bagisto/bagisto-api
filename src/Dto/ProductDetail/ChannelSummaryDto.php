<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class ChannelSummaryDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $code = null,
        public ?string $hostname = null,
        public ?string $currency_code = null,
        public ?string $locale_code = null,
    ) {}
}
