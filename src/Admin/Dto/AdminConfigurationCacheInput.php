<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for the cache management action (POST /configuration/cache, createAdminConfigurationCache).
 */
class AdminConfigurationCacheInput
{
    #[ApiProperty(description: 'Cache action key, e.g. `clear-config` or `build-routes`.')]
    #[Groups(['mutation'])]
    public ?string $action = null;
}
