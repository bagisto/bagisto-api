<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for the `createAdminDraftCart` GraphQL mutation.
 *
 * REST path: POST /api/admin/customers/{customerId}/draft-carts
 * (customerId comes from the URL on REST).
 */
class AdminCreateDraftCartInput
{
    #[ApiProperty(description: 'Customer ID the draft cart will belong to.')]
    #[Groups(['mutation'])]
    public ?int $customerId = null;
}
