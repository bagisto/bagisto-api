<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSearchTermCreateInput
{
    /**
     * The term.
     */
    #[ApiProperty(description: 'Search term text.')]
    #[Groups(['mutation'])]
    public ?string $term = null;

    /**
     * The redirect url.
     */
    #[ApiProperty(description: 'Optional redirect URL (http/https).')]
    #[Groups(['mutation'])]
    public ?string $redirect_url = null;

    /**
     * The channel id.
     */
    #[ApiProperty(description: 'Channel id the term belongs to.')]
    #[Groups(['mutation'])]
    public ?int $channel_id = null;

    /**
     * The locale.
     */
    #[ApiProperty(description: 'Locale code (e.g. en).')]
    #[Groups(['mutation'])]
    public ?string $locale = null;
}
