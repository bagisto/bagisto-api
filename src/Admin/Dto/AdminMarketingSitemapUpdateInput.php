<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSitemapUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/sitemaps/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The file name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $file_name = null;

    /**
     * The path.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $path = null;

    /**
     * The ids of the channels the sitemap covers, replacing the current list.
     *
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Channel ids the sitemap covers. The supplied list replaces the current one.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;
}
