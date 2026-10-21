<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSitemapCreateInput
{
    /**
     * The file name.
     */
    #[ApiProperty(description: 'File name; must end in .xml (e.g. sitemap.xml).')]
    #[Groups(['mutation'])]
    public ?string $file_name = null;

    /**
     * The path.
     */
    #[ApiProperty(description: 'Storage directory path; must start and end with / (e.g. /).')]
    #[Groups(['mutation'])]
    public ?string $path = null;

    /**
     * The ids of the channels the sitemap covers.
     *
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Channel ids the sitemap covers. At least one is required — a sitemap with no channel generates nothing.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;
}
