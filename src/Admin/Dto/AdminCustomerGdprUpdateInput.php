<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerGdprUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/customers/gdpr-requests/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The status.
     */
    #[ApiProperty(description: 'pending | processing | declined | approved | revoked')]
    #[Groups(['mutation'])]
    public ?string $status = null;

    /**
     * The message.
     */
    #[ApiProperty(description: 'Optional message recorded with the status change (e.g. reason for decline). Stored in the request message field.')]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
