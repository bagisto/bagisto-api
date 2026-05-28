<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for the admin Reorder action.
 *
 * REST takes the order id from the URL path; this DTO exists to give the
 * GraphQL mutation a typed input.
 */
class AdminReorderInput
{
    #[Groups(['mutation'])]
    public ?string $orderId = null;
}
