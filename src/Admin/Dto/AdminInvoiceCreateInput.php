<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminInvoiceCreateInput
{
    #[ApiProperty(description: 'Order id (GraphQL only — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $orderId = null;

    /**
     * Array of `{ orderItemId, quantity }` objects. Typed as plain `array` so
     * API Platform's Symfony denormalizer doesn't try to map elements onto
     * a nested resource (which it rejects with "Nested documents are not
     * allowed"). The processor reads each entry as an assoc/array.
     */
    #[ApiProperty(description: 'Items to invoice: array of { orderItemId, quantity }.')]
    #[Groups(['mutation'])]
    public array $items = [];
}
