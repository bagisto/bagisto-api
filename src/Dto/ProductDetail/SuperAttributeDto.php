<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class SuperAttributeDto
{
    /**
     * @param  SuperAttributeOptionDto[]  $options
     */
    public function __construct(
        public ?int $id = null,
        public ?string $code = null,
        public ?string $label = null,
        public ?string $type = null,
        #[ApiProperty(readableLink: true)]
        public array $options = [],
    ) {}
}
