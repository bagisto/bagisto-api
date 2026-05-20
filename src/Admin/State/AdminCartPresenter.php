<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Checkout\Models\CartItem;

/**
 * Maps a Cart model + its children to the AdminCart resource shape used by
 * every /api/admin/carts/* endpoint. Shared by the GET endpoint and by every
 * write processor (add item, update qty, save address, apply coupon, ...) so
 * a single response shape is guaranteed across the cart group.
 *
 * Items and addresses are rendered as plain associative arrays — declared as
 * `?array` / `array` on `AdminCart` — so API Platform's serializer keeps
 * them inline rather than collapsing to IRIs (the auto-discovered DTO
 * approach surfaced nested resources as "/api/admin_cart_item_dtos/{id}"
 * strings; plain arrays sidestep that).
 */
class AdminCartPresenter
{
    public static function present(Cart $cart, ?bool $success = null, ?string $message = null): AdminCart
    {
        $cart->load([
            'items.product',
            'items.child',
            'items.children',
            'billing_address',
            'shipping_address',
            'payment',
        ]);

        $dto = new AdminCart;

        $dto->id = $cart->id;
        $dto->customerId = $cart->customer_id;
        $dto->isGuest = (bool) $cart->is_guest;
        $dto->isActive = (bool) $cart->is_active;
        $dto->itemsCount = $cart->items_count !== null ? (int) $cart->items_count : null;
        $dto->itemsQty = $cart->items_qty !== null ? (int) $cart->items_qty : null;

        $dto->subTotal = self::nullableFloat($cart->base_sub_total);
        $dto->formattedSubTotal = core()->formatPrice($cart->base_sub_total ?? 0);
        $dto->grandTotal = self::nullableFloat($cart->base_grand_total);
        $dto->formattedGrandTotal = core()->formatPrice($cart->base_grand_total ?? 0);
        $dto->shippingAmount = self::nullableFloat($cart->base_shipping_amount);
        $dto->formattedShippingAmount = core()->formatPrice($cart->base_shipping_amount ?? 0);
        $dto->taxTotal = self::nullableFloat($cart->base_tax_total);
        $dto->formattedTaxTotal = core()->formatPrice($cart->base_tax_total ?? 0);
        $dto->discountAmount = self::nullableFloat($cart->base_discount_amount);
        $dto->formattedDiscountAmount = core()->formatPrice($cart->base_discount_amount ?? 0);

        $dto->couponCode = $cart->coupon_code;
        $dto->shippingMethod = $cart->shipping_method;

        $paymentMethod = $cart->payment?->method;
        $dto->paymentMethod = $paymentMethod;
        $dto->paymentMethodTitle = $paymentMethod
            ? (core()->getConfigData('sales.payment_methods.'.$paymentMethod.'.title') ?: $paymentMethod)
            : null;

        $dto->haveStockableItems = (bool) $cart->haveStockableItems();

        $dto->items = $cart->items
            ->whereNull('parent_id')
            ->values()
            ->map(fn (CartItem $item) => self::toItemArray($item))
            ->all();

        $dto->billingAddress = self::toAddressArray($cart->billing_address);
        $dto->shippingAddress = self::toAddressArray($cart->shipping_address);

        $dto->success = $success;
        $dto->message = $message;

        return $dto;
    }

    protected static function toItemArray(CartItem $item, bool $withChildren = true): array
    {
        $row = [
            'id'                      => $item->id,
            'cartId'                  => $item->cart_id,
            'productId'               => $item->product_id,
            'parentId'                => $item->parent_id,
            'sku'                     => $item->sku,
            'type'                    => $item->type,
            'name'                    => $item->name,
            'quantity'                => (int) $item->quantity,
            'price'                   => self::nullableFloat($item->base_price),
            'formattedPrice'          => core()->formatPrice($item->base_price ?? 0),
            'total'                   => self::nullableFloat($item->base_total),
            'formattedTotal'          => core()->formatPrice($item->base_total ?? 0),
            'taxAmount'               => self::nullableFloat($item->base_tax_amount),
            'formattedTaxAmount'      => core()->formatPrice($item->base_tax_amount ?? 0),
            'discountAmount'          => self::nullableFloat($item->base_discount_amount),
            'formattedDiscountAmount' => core()->formatPrice($item->base_discount_amount ?? 0),
            'additional'              => is_array($item->additional) ? $item->additional : null,
            'child'                   => null,
            'children'                => [],
        ];

        if ($withChildren) {
            if ($item->child) {
                $row['child'] = self::toItemArray($item->child, false);
            }
            if ($item->children) {
                $row['children'] = $item->children->map(fn (CartItem $c) => self::toItemArray($c, false))->all();
            }
        }

        return $row;
    }

    protected static function toAddressArray(?CartAddress $address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'id'          => $address->id,
            'addressType' => $address->address_type,
            'firstName'   => $address->first_name,
            'lastName'    => $address->last_name,
            'companyName' => $address->company_name,
            'email'       => $address->email,
            'address'     => is_array($address->address) ? implode("\n", $address->address) : (string) $address->address,
            'city'        => $address->city,
            'state'       => $address->state,
            'country'     => $address->country,
            'postcode'    => $address->postcode,
            'phone'       => $address->phone,
        ];
    }

    protected static function nullableFloat($value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
