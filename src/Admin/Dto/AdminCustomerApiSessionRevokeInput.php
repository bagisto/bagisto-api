<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminCustomerApiSessionRevokeInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The customer whose sessions are ended.
     */
    #[ApiProperty(description: 'The customer ID; over REST it comes from the path.', example: 1529)]
    public ?int $customer_id = null;
}
