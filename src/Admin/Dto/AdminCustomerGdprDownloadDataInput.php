<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * POST /api/admin/customers/{customerId}/gdpr-download-data +
 * createAdminCustomerGdprDownloadData mutation input.
 *
 * Returns a JSON dump of every table that references the customer's id.
 * Not bound to a GDPR request — admin can run ad-hoc on any customer.
 */
class AdminCustomerGdprDownloadDataInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $customerId = null;
}
