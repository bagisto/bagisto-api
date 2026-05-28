<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * POST /api/admin/customers/gdpr-requests/{id}/process +
 * createAdminCustomerGdprProcess mutation input.
 *
 * Approves a GDPR request and runs the destructive side effect:
 *   - type = delete → cascade-deletes the customer
 *   - type = update → marks request approved (admin then applies the changes manually)
 *
 * Status is set to "approved" on success.
 */
class AdminCustomerGdprProcessInput
{
    #[ApiProperty(description: 'GDPR request id (numeric or IRI). Required for GraphQL; REST takes it from the URL.')]
    #[Groups(['mutation'])]
    public ?string $requestId = null;

    #[ApiProperty(description: 'Optional admin note recorded alongside the approval.')]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
