<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\GuestCartTokens;
use Webkul\Checkout\Models\Cart;
use Webkul\Tax\Facades\Tax;

class CartData
{
    /**
     * The id.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    /**
     * The cart token.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Cart token for guest users')]
    public ?string $cartToken = null;

    /**
     * The customer id.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $customerId = null;

    /**
     * The channel id.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $channelId = null;

    /**
     * The items count.
     */
    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    /**
     * Individual cart items - array of CartItemData DTO objects.
     *
     * @var array<CartItemData>|null
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Individual cart items')]
    public ?array $items = [];

    /**
     * The subtotal.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Subtotal before discounts and taxes')]
    public ?float $subtotal = null;

    /**
     * The base subtotal.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseSubtotal = null;

    /**
     * The discount amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Total discount amount')]
    public ?float $discountAmount = null;

    /**
     * The base discount amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    /**
     * The tax amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Total tax amount')]
    public ?float $taxAmount = null;

    /**
     * The base tax amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    /**
     * The shipping amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Shipping cost')]
    public ?float $shippingAmount = null;

    /**
     * The base shipping amount.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseShippingAmount = null;

    /**
     * The grand total.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Grand total')]
    public ?float $grandTotal = null;

    /**
     * The base grand total.
     */
    #[Groups(['query', 'mutation'])]
    public ?float $baseGrandTotal = null;

    /**
     * The formatted subtotal.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted subtotal price')]
    public ?string $formattedSubtotal = null;

    /**
     * The formatted discount amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted discount amount')]
    public ?string $formattedDiscountAmount = null;

    /**
     * The formatted tax amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted tax amount')]
    public ?string $formattedTaxAmount = null;

    /**
     * The formatted shipping amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted shipping amount')]
    public ?string $formattedShippingAmount = null;

    /**
     * The formatted grand total.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted grand total price')]
    public ?string $formattedGrandTotal = null;

    /**
     * The coupon code.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Applied coupon code')]
    public ?string $couponCode = null;

    /**
     * The mini cart offer info enabled.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'True when the mini cart is shown and an offer text is configured')]
    public ?bool $miniCartOfferInfoEnabled = null;

    /**
     * The mini cart offer info.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Mini cart offer text for the current locale')]
    public ?string $miniCartOfferInfo = null;

    /**
     * The success.
     */
    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    /**
     * The carts.
     */
    #[Groups(['query', 'mutation'])]
    public ?array $carts = null;

    /**
     * The session token.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Unique session token for guest cart')]
    public ?string $sessionToken = null;

    /**
     * The is guest.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Is this a guest cart')]
    public bool $isGuest = false;

    /**
     * The items qty.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Total quantity of all items')]
    public ?int $itemsQty = null;

    /**
     * The sub total incl tax.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Subtotal including tax')]
    public ?float $subTotalInclTax = null;

    /**
     * The base sub total incl tax.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Base subtotal including tax')]
    public ?float $baseSubTotalInclTax = null;

    /**
     * The formatted sub total incl tax.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted subtotal including tax')]
    public ?string $formattedSubTotalInclTax = null;

    /**
     * The tax total.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Tax total')]
    public ?float $taxTotal = null;

    /**
     * The formatted tax total.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted tax total')]
    public ?string $formattedTaxTotal = null;

    /**
     * The shipping amount incl tax.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Shipping amount including tax')]
    public ?float $shippingAmountInclTax = null;

    /**
     * The base shipping amount incl tax.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Base shipping amount including tax')]
    public ?float $baseShippingAmountInclTax = null;

    /**
     * The formatted shipping amount incl tax.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted shipping amount including tax')]
    public ?string $formattedShippingAmountInclTax = null;

    /**
     * The items discount amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Sum of the discount amounts on the cart items')]
    public ?float $itemsDiscountAmount = null;

    /**
     * The formatted items discount amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted items discount amount')]
    public ?string $formattedItemsDiscountAmount = null;

    /**
     * The shipping discount amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Discount amount on the selected shipping rate')]
    public ?float $shippingDiscountAmount = null;

    /**
     * The formatted shipping discount amount.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted shipping discount amount')]
    public ?string $formattedShippingDiscountAmount = null;

    /**
     * The billing address.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Billing address')]
    public ?array $billingAddress = null;

    /**
     * The shipping address.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Shipping address')]
    public ?array $shippingAddress = null;

    /**
     * The applied taxes.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Applied taxes')]
    public ?array $appliedTaxes = null;

    /**
     * The have stockable items.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Has stockable items')]
    public ?bool $haveStockableItems = null;

    /**
     * The payment method.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Payment method code')]
    public ?string $paymentMethod = null;

    /**
     * The payment method title.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Payment method title')]
    public ?string $paymentMethodTitle = null;

    /**
     * The selected shipping rate.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false, description: 'Selected shipping rate')]
    public ?string $selectedShippingRate = null;

    /**
     * The selected shipping rate title.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false, description: 'Selected shipping rate title')]
    public ?string $selectedShippingRateTitle = null;

    /**
     * The payment redirect url.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Payment redirect URL (if payment gateway redirect needed)')]
    public ?string $paymentRedirectUrl = null;

    /**
     * The order id.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Order ID after order creation')]
    public ?string $orderId = null;

    /**
     * The redirect uri.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Redirect URL for buy now checkout')]
    public ?string $redirectUri = null;

    /**
     * The redirect.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'True when the selected payment method requires the shopper to be sent to a payment page before the order is created')]
    public ?bool $redirect = false;

    /**
     * The redirect url.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Redirect URL for payment gateway')]
    public ?string $redirectUrl = null;

    /**
     * The order increment id.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Order ID after successful order creation')]
    public ?string $orderIncrementId = null;

    /**
     * Get selected shipping rate.
     */
    public function getSelectedShippingRate(): ?string
    {
        return $this->selectedShippingRate;
    }

    /**
     * Set selected shipping rate.
     */
    public function setSelectedShippingRate(?string $selectedShippingRate): void
    {
        $this->selectedShippingRate = $selectedShippingRate;
    }

    /**
     * Get selected shipping rate title.
     */
    public function getSelectedShippingRateTitle(): ?string
    {
        return $this->selectedShippingRateTitle;
    }

    /**
     * Set selected shipping rate title.
     */
    public function setSelectedShippingRateTitle(?string $selectedShippingRateTitle): void
    {
        $this->selectedShippingRateTitle = $selectedShippingRateTitle;
    }

    /**
     * Fill the mini cart offer text the admin sets under Sales, Checkout, Mini Cart.
     */
    public static function withMiniCartOffer(self $data): self
    {
        $offer = trim((string) core()->getConfigData('sales.checkout.mini_cart.offer_info'));

        $data->miniCartOfferInfoEnabled = (bool) core()->getConfigData('sales.checkout.mini_cart.display_mini_cart') && $offer !== '';
        $data->miniCartOfferInfo = $offer !== '' ? $offer : null;

        return $data;
    }

    /**
     * From model.
     */
    public static function fromModel(Cart $cart): self
    {
        $data = self::withMiniCartOffer(new self);

        $data->id = $cart->id;
        $data->cartToken = self::ownedCartToken($cart);
        $data->customerId = $cart->customer_id;
        $data->isGuest = ! $cart->customer_id;
        $data->channelId = $cart->channel_id;
        $data->itemsCount = $cart->items()->count();
        $data->itemsQty = (int) ($cart->items_qty ?? 0);

        $items = $cart->items()
            ->with(['product'])
            ->get()
            ->map(fn ($item) => CartItemData::fromModel($item));

        $data->items = $items->toArray();

        $data->subtotal = (float) core()->convertPrice($cart->base_sub_total ?? 0);
        $data->baseSubtotal = (float) ($cart->base_sub_total ?? 0);
        $data->formattedSubtotal = core()->currency($cart->base_sub_total ?? 0);

        $data->subTotalInclTax = (float) core()->convertPrice($cart->base_sub_total_incl_tax ?? $cart->base_sub_total ?? 0);
        $data->baseSubTotalInclTax = (float) ($cart->base_sub_total_incl_tax ?? $cart->base_sub_total ?? 0);
        $data->formattedSubTotalInclTax = core()->currency($cart->base_sub_total_incl_tax ?? $cart->base_sub_total ?? 0);

        $data->taxAmount = (float) core()->convertPrice($cart->base_tax_total ?? 0);
        $data->baseTaxAmount = (float) ($cart->base_tax_total ?? 0);
        $data->taxTotal = (float) core()->convertPrice($cart->base_tax_total ?? 0);
        $data->formattedTaxTotal = core()->currency($cart->base_tax_total ?? 0);
        $data->formattedTaxAmount = core()->currency($cart->base_tax_total ?? 0);

        $data->discountAmount = (float) core()->convertPrice($cart->base_discount_amount ?? 0);
        $data->baseDiscountAmount = (float) ($cart->base_discount_amount ?? 0);
        $data->formattedDiscountAmount = core()->currency($cart->base_discount_amount ?? 0);

        $data->shippingAmount = (float) core()->convertPrice($cart->base_shipping_amount ?? 0);
        $data->baseShippingAmount = (float) ($cart->base_shipping_amount ?? 0);
        $data->formattedShippingAmount = core()->currency($cart->base_shipping_amount ?? 0);

        $data->shippingAmountInclTax = (float) core()->convertPrice($cart->base_shipping_amount_incl_tax ?? $cart->base_shipping_amount ?? 0);
        $data->baseShippingAmountInclTax = (float) ($cart->base_shipping_amount_incl_tax ?? $cart->base_shipping_amount ?? 0);
        $data->formattedShippingAmountInclTax = core()->currency($cart->base_shipping_amount_incl_tax ?? $cart->base_shipping_amount ?? 0);

        $itemsDiscount = (float) $cart->items()->sum('base_discount_amount');
        $data->itemsDiscountAmount = (float) core()->convertPrice($itemsDiscount);
        $data->formattedItemsDiscountAmount = core()->currency($itemsDiscount);

        $shippingDiscount = (float) ($cart->selected_shipping_rate?->base_discount_amount ?? 0);
        $data->shippingDiscountAmount = (float) core()->convertPrice($shippingDiscount);
        $data->formattedShippingDiscountAmount = core()->currency($shippingDiscount);

        $data->grandTotal = (float) core()->convertPrice($cart->base_grand_total ?? 0);
        $data->baseGrandTotal = (float) ($cart->base_grand_total ?? 0);
        $data->formattedGrandTotal = core()->currency($cart->base_grand_total ?? 0);

        $data->couponCode = $cart->coupon_code ?: null;

        if ($cart->billing_address) {
            $data->billingAddress = [
                'id' => $cart->billing_address->id,
                'firstName' => $cart->billing_address->first_name,
                'lastName' => $cart->billing_address->last_name,
                'email' => $cart->billing_address->email,
                'address' => $cart->billing_address->address,
                'city' => $cart->billing_address->city,
                'state' => $cart->billing_address->state,
                'country' => $cart->billing_address->country,
                'postcode' => $cart->billing_address->postcode,
                'phone' => $cart->billing_address->phone,
            ];
        }

        if ($cart->shipping_address) {
            $data->shippingAddress = [
                'id' => $cart->shipping_address->id,
                'firstName' => $cart->shipping_address->first_name,
                'lastName' => $cart->shipping_address->last_name,
                'email' => $cart->shipping_address->email,
                'address' => $cart->shipping_address->address,
                'city' => $cart->shipping_address->city,
                'state' => $cart->shipping_address->state,
                'country' => $cart->shipping_address->country,
                'postcode' => $cart->shipping_address->postcode,
                'phone' => $cart->shipping_address->phone,
            ];
        }

        if ($cart->payment) {
            $data->paymentMethod = $cart->payment->method;
            $data->paymentMethodTitle = core()->getConfigData('sales.payment_methods.'.$cart->payment->method.'.title');
        }

        try {
            $taxes = collect(Tax::getTaxRatesWithAmount($cart, true))->map(function ($rate) {
                return core()->currency($rate ?? 0);
            })->toArray();
            $data->appliedTaxes = $taxes;
        } catch (\Exception $exception) {
            $data->appliedTaxes = [];
        }

        $data->haveStockableItems = $cart->haveStockableItems();

        if ($cart->selected_shipping_rate) {
            $data->selectedShippingRate = $cart->selected_shipping_rate->method ?? null;
            $data->selectedShippingRateTitle = $cart->selected_shipping_rate->method_title ?? null;
        } else {
            $data->selectedShippingRate = null;
            $data->selectedShippingRateTitle = null;
        }

        return $data;
    }

    /**
     * Collection.
     */
    public static function collection(iterable $carts): array
    {
        $cartDataCollection = [];
        foreach ($carts as $cart) {
            $cartDataCollection[] = self::fromModel($cart);
        }

        return $cartDataCollection;
    }

    /**
     * Get items.
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * Set items.
     */
    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    /**
     * Return the cart's guest token only to a caller who authenticated with that same token.
     */
    protected static function ownedCartToken(Cart $cart): ?string
    {
        $guestToken = GuestCartTokens::where('cart_id', $cart->id)->orderByDesc('id')->value('token');

        $bearer = TokenHeaderFacade::getAuthorizationBearerToken(request());

        if (
            ! $guestToken
            || ! $bearer
            || ! hash_equals((string) $guestToken, $bearer)
        ) {
            return null;
        }

        return $guestToken;
    }
}
