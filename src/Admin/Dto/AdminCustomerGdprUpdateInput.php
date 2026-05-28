<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * PUT /api/admin/customers/gdpr-requests/{id} +
 * updateAdminCustomerGdprRequest mutation input.
 */
class AdminCustomerGdprUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/customers/gdpr-requests/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'pending | processing | declined | approved | revoked')]
    #[Groups(['mutation'])]
    public ?string $status = null;

    #[ApiProperty(description: 'Optional message recorded with the status change (e.g. reason for decline). Stored in the request message field.')]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
