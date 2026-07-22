<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Models\GuestCartTokens;
use Webkul\Checkout\Models\Cart;
use Webkul\Tax\Facades\Tax;

/**
 * Shopping cart data transfer object
 */
class CartData
{
    #[Groups(['query', 'mutation'])]
    public ?int $id = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Cart token for guest users')]
    public ?string $cartToken = null;

    #[Groups(['query', 'mutation'])]
    public ?int $customerId = null;

    #[Groups(['query', 'mutation'])]
    public ?int $channelId = null;

    #[Groups(['query', 'mutation'])]
    public ?int $itemsCount = null;

    /**
     * Individual cart items - array of CartItemData DTO objects
     *
     * @var array<CartItemData>|null
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Individual cart items')]
    public ?array $items = [];

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Subtotal before discounts and taxes')]
    public ?float $subtotal = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseSubtotal = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Total discount amount')]
    public ?float $discountAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseDiscountAmount = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Total tax amount')]
    public ?float $taxAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseTaxAmount = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Shipping cost')]
    public ?float $shippingAmount = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseShippingAmount = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Grand total')]
    public ?float $grandTotal = null;

    #[Groups(['query', 'mutation'])]
    public ?float $baseGrandTotal = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted subtotal price')]
    public ?string $formattedSubtotal = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted discount amount')]
    public ?string $formattedDiscountAmount = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted tax amount')]
    public ?string $formattedTaxAmount = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted shipping amount')]
    public ?string $formattedShippingAmount = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted grand total price')]
    public ?string $formattedGrandTotal = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Applied coupon code')]
    public ?string $couponCode = null;

    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    #[Groups(['query', 'mutation'])]
    public ?string $message = null;

    #[Groups(['query', 'mutation'])]
    public ?array $carts = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Unique session token for guest cart')]
    public ?string $sessionToken = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Is this a guest cart')]
    public bool $isGuest = false;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Total quantity of all items')]
    public ?int $itemsQty = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Subtotal including tax')]
    public ?float $subTotalInclTax = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Base subtotal including tax')]
    public ?float $baseSubTotalInclTax = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted subtotal including tax')]
    public ?string $formattedSubTotalInclTax = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Tax total')]
    public ?float $taxTotal = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted tax total')]
    public ?string $formattedTaxTotal = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Shipping amount including tax')]
    public ?float $shippingAmountInclTax = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Base shipping amount including tax')]
    public ?float $baseShippingAmountInclTax = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Formatted shipping amount including tax')]
    public ?string $formattedShippingAmountInclTax = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Billing address')]
    public ?array $billingAddress = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Shipping address')]
    public ?array $shippingAddress = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Applied taxes')]
    public ?array $appliedTaxes = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Has stockable items')]
    public ?bool $haveStockableItems = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Payment method code')]
    public ?string $paymentMethod = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Payment method title')]
    public ?string $paymentMethodTitle = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false, description: 'Selected shipping rate')]
    public ?string $selectedShippingRate = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false, description: 'Selected shipping rate title')]
    public ?string $selectedShippingRateTitle = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Payment redirect URL (if payment gateway redirect needed)')]
    public ?string $paymentRedirectUrl = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Order ID after order creation')]
    public ?string $orderId = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Redirect URL for buy now checkout')]
    public ?string $redirectUri = null;

    public function getSelectedShippingRate(): ?string
    {
        return $this->selectedShippingRate;
    }

    public function setSelectedShippingRate(?string $selectedShippingRate): void
    {
        $this->selectedShippingRate = $selectedShippingRate;
    }

    public function getSelectedShippingRateTitle(): ?string
    {
        return $this->selectedShippingRateTitle;
    }

    public function setSelectedShippingRateTitle(?string $selectedShippingRateTitle): void
    {
        $this->selectedShippingRateTitle = $selectedShippingRateTitle;
    }

    public static function fromModel(Cart $cart): self
    {
        $data = new self;

        $data->id = $cart->id;
        $data->cartToken = GuestCartTokens::where('cart_id', $cart->id)->orderByDesc('id')->value('token');
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

        $data->taxAmount = (float) core()->convertPrice($cart->base_tax_amount ?? 0);
        $data->baseTaxAmount = (float) ($cart->base_tax_amount ?? 0);
        $data->taxTotal = (float) core()->convertPrice($cart->base_tax_total ?? $cart->base_tax_amount ?? 0);
        $data->formattedTaxTotal = core()->currency($cart->base_tax_total ?? $cart->base_tax_amount ?? 0);
        $data->formattedTaxAmount = core()->currency($cart->base_tax_amount ?? 0);

        $data->discountAmount = (float) core()->convertPrice($cart->base_discount_amount ?? 0);
        $data->baseDiscountAmount = (float) ($cart->base_discount_amount ?? 0);
        $data->formattedDiscountAmount = core()->currency($cart->base_discount_amount ?? 0);

        $data->shippingAmount = (float) core()->convertPrice($cart->base_shipping_amount ?? 0);
        $data->baseShippingAmount = (float) ($cart->base_shipping_amount ?? 0);
        $data->formattedShippingAmount = core()->currency($cart->base_shipping_amount ?? 0);

        $data->shippingAmountInclTax = (float) core()->convertPrice($cart->base_shipping_amount_incl_tax ?? $cart->base_shipping_amount ?? 0);
        $data->baseShippingAmountInclTax = (float) ($cart->base_shipping_amount_incl_tax ?? $cart->base_shipping_amount ?? 0);
        $data->formattedShippingAmountInclTax = core()->currency($cart->base_shipping_amount_incl_tax ?? $cart->base_shipping_amount ?? 0);

        $data->grandTotal = (float) core()->convertPrice($cart->base_grand_total ?? 0);
        $data->baseGrandTotal = (float) ($cart->base_grand_total ?? 0);
        $data->formattedGrandTotal = core()->currency($cart->base_grand_total ?? 0);

        // Bug fix: coupon_code lives on the carts.coupon_code column directly, not in the additional JSON
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
        } catch (\Exception $e) {
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

    public static function collection(iterable $carts): array
    {
        $cartDataCollection = [];
        foreach ($carts as $cart) {
            $cartDataCollection[] = self::fromModel($cart);
        }

        return $cartDataCollection;
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'True when the selected payment method requires the shopper to be sent to a payment page before the order is created')]
    public ?bool $redirect = false;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Redirect URL for payment gateway')]
    public ?string $redirectUrl = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(description: 'Order ID after successful order creation')]
    public ?string $orderIncrementId = null;
}
