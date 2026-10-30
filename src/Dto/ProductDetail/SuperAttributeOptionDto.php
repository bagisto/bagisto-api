<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class SuperAttributeOptionDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $label = null,
        #[ApiProperty(description: 'The colour hex for a color swatch, the label for a text swatch, or the stored image path for an image swatch.')]
        public ?string $swatch_value = null,
        #[ApiProperty(description: 'The image swatch URL; null for other swatch types.')]
        public ?string $swatch_value_url = null,
        #[ApiProperty(description: 'Alt text for an image swatch, in the requested locale.')]
        public ?string $swatch_alt = null,
        /**
         * @var array<int, int>
         */
        #[ApiProperty(description: 'Ids of the saleable variants that carry this option.')]
        public array $products = [],
    ) {}
}
