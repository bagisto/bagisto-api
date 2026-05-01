<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class DownloadableLinkDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $sort_order = null,
        public ?string $type = null,
        public ?string $file_type = null,
        public ?string $url_or_path = null,
        public ?string $sample_type = null,
        public ?string $sample_file_type = null,
        public ?string $sample_url_or_path = null,
        public ?int $downloads = null,
        public ?float $price = null,
        public ?string $formatted_price = null,
        public ?string $title = null,
    ) {}
}
