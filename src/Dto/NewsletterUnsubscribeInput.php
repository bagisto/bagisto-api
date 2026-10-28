<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class NewsletterUnsubscribeInput
{
    #[ApiProperty(description: 'Unsubscribe token from the newsletter email link (/subscription/{token}).')]
    #[Groups(['mutation'])]
    public ?string $token = null;
}
