<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerGdprProcessInput
{
    /**
     * The request id.
     */
    #[ApiProperty(description: 'GDPR request id (numeric or IRI). Required for GraphQL; REST takes it from the URL.')]
    #[Groups(['mutation'])]
    public ?string $requestId = null;

    /**
     * The message.
     */
    #[ApiProperty(description: 'Optional admin note recorded alongside the approval.')]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
