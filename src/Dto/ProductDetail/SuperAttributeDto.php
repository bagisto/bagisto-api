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
        #[ApiProperty(description: 'How the storefront renders the options: dropdown, color, image or text. Null renders as a dropdown.')]
        public ?string $swatch_type = null,
        #[ApiProperty(readableLink: true)]
        public array $options = [],
    ) {}
}
