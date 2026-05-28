<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for POST /api/admin/carts/{id}/coupon and the GraphQL
 * createAdminCartApplyCoupon mutation. Also accepted by the remove-coupon
 * mutation (where `code` is ignored — the cart's currently applied coupon
 * is removed).
 */
class AdminCartCouponInput
{
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    #[Groups(['mutation'])]
    public ?string $code = null;
}
