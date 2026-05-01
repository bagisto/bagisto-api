<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class DownloadableSampleDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $sort_order = null,
        public ?string $type = null,
        public ?string $file_type = null,
        public ?string $url_or_path = null,
        public ?string $title = null,
    ) {}
}
