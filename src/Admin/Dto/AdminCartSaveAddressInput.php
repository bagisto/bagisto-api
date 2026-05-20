<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for POST /api/admin/carts/{id}/addresses and the GraphQL
 * createAdminCartSaveAddress mutation.
 *
 * Body shape mirrors the monolith CartAddressRequest:
 *   {
 *     "billing":  { firstName, lastName, email, address:[...], city, ..., useForShipping?: bool },
 *     "shipping": { firstName, ... }   // omit if billing.useForShipping = true
 *   }
 *
 * The processor passes the whole body straight into Cart::saveAddresses() so
 * any extra fields (e.g. company_name, address2) flow through verbatim.
 */
class AdminCartSaveAddressInput
{
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    #[Groups(['mutation'])]
    public ?array $billing = null;

    #[Groups(['mutation'])]
    public ?array $shipping = null;
}
