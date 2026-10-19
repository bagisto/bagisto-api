<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [])]
class DownloadLinkOutput
{
    /**
     * Create a new instance.
     */
    public function __construct(
        #[ApiProperty(identifier: true, writable: false)]
        public string $id = '1',

        #[ApiProperty(description: 'Temporary download token')]
        public ?string $token = null,

        #[ApiProperty(description: 'Download URL')]
        public ?string $url = null,

        #[ApiProperty(description: 'Token expiration timestamp')]
        public ?string $expiresAt = null,
    ) {}
}
