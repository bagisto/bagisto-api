<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for removing a Create Order sidebar item: `id` is /api/admin/customers/{customerId}/<list>/{itemId}.
 */
class AdminCustomerSidebarItemRemoveInput
{
    #[ApiProperty(description: 'The item path, e.g. /api/admin/customers/5/cart-items/1701.')]
    #[Groups(['mutation'])]
    public ?string $id = null;
}
