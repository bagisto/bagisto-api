<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Paypal\Payment\SmartButton;

/**
 * Builds and checks the PayPal order of the Smart Button, as core's SmartButtonController does.
 */
trait BuildsPaypalSmartButtonOrder
{
    /**
     * Stop when the merchant has not entered the PayPal REST credentials.
     */
    protected function assertSmartButtonConfigured(SmartButton $smartButton): void
    {
        if (! $smartButton->getConfigData('client_id') || ! $smartButton->getConfigData('client_secret')) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-configured'), 422);
        }
    }

    /**
     * The order body core sends to PayPal, plus the cart id so the payment can be traced back to this cart.
     */
    protected function smartButtonRequestBody(SmartButton $smartButton, object $cart): array
    {
        $billingAddressLines = $this->smartButtonAddressLines($cart->billing_address->address);

        $data = [
            'intent' => 'CAPTURE',

            'payer' => [
                'name' => [
                    'given_name' => $cart->billing_address->first_name,
                    'surname' => $cart->billing_address->last_name,
                ],

                'address' => [
                    'address_line_1' => current($billingAddressLines),
                    'address_line_2' => last($billingAddressLines),
                    'admin_area_2' => $cart->billing_address->city,
                    'admin_area_1' => $cart->billing_address->state,
                    'postal_code' => $cart->billing_address->postcode,
                    'country_code' => $cart->billing_address->country,
                ],

                'email_address' => $cart->billing_address->email,
            ],

            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
            ],

            'purchase_units' => [
                [
                    'custom_id' => (string) $cart->id,

                    'amount' => [
                        'value' => $this->smartButtonAmount($smartButton, $cart),
                        'currency_code' => $cart->cart_currency_code,

                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $smartButton->formatCurrencyValue((float) $cart->sub_total),
                            ],

                            'shipping' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $smartButton->formatCurrencyValue((float) ($cart->selected_shipping_rate?->price ?? 0)),
                            ],

                            'tax_total' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $smartButton->formatCurrencyValue((float) $cart->tax_total),
                            ],

                            'discount' => [
                                'currency_code' => $cart->cart_currency_code,
                                'value' => $smartButton->formatCurrencyValue((float) $cart->discount_amount),
                            ],
                        ],
                    ],

                    'items' => $this->smartButtonLineItems($smartButton, $cart),
                ],
            ],
        ];

        if (! empty($cart->billing_address->phone)) {
            $data['payer']['phone'] = [
                'phone_type' => 'MOBILE',

                'phone_number' => [
                    'national_number' => $smartButton->formatPhone($cart->billing_address->phone),
                ],
            ];
        }

        if (
            $cart->haveStockableItems()
            && $cart->shipping_address
        ) {
            $shippingAddressLines = $this->smartButtonAddressLines($cart->shipping_address->address);

            $data['application_context']['shipping_preference'] = 'SET_PROVIDED_ADDRESS';

            $data['purchase_units'][0]['shipping'] = [
                'address' => [
                    'address_line_1' => current($shippingAddressLines),
                    'address_line_2' => last($shippingAddressLines),
                    'admin_area_2' => $cart->shipping_address->city,
                    'admin_area_1' => $cart->shipping_address->state,
                    'postal_code' => $cart->shipping_address->postcode,
                    'country_code' => $cart->shipping_address->country,
                ],
            ];
        }

        return $data;
    }

    /**
     * The total core asks PayPal to collect for the cart.
     */
    protected function smartButtonAmount(SmartButton $smartButton, object $cart): float
    {
        return $smartButton->formatCurrencyValue(
            (float) $cart->sub_total
            + $cart->tax_total
            + ($cart->selected_shipping_rate?->price ?? 0)
            - $cart->discount_amount
        );
    }

    /**
     * Whether the PayPal order was created for this cart, in its currency and for its total.
     */
    protected function smartButtonOrderMatchesCart(SmartButton $smartButton, array $paypalOrder, object $cart): bool
    {
        $purchaseUnit = $paypalOrder['purchase_units'][0] ?? [];

        return (string) ($purchaseUnit['custom_id'] ?? '') === (string) $cart->id
            && ($purchaseUnit['amount']['currency_code'] ?? null) === $cart->cart_currency_code
            && abs((float) ($purchaseUnit['amount']['value'] ?? 0) - $this->smartButtonAmount($smartButton, $cart)) < 0.005;
    }

    /**
     * The PayPal SDK returns model objects; core reads them as arrays the same way.
     */
    protected function smartButtonArray(mixed $result): array
    {
        return (array) json_decode(json_encode($result), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function smartButtonLineItems(SmartButton $smartButton, object $cart): array
    {
        $lineItems = [];

        foreach ($cart->items as $item) {
            $lineItems[] = [
                'unit_amount' => [
                    'currency_code' => $cart->cart_currency_code,
                    'value' => $smartButton->formatCurrencyValue((float) $item->price),
                ],
                'quantity' => $item->quantity,
                'name' => $item->name,
                'sku' => $item->sku,
                'category' => $item->getTypeInstance()->isStockable() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS',
            ];
        }

        return $lineItems;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function smartButtonAddressLines(?string $address): array
    {
        $address = explode(PHP_EOL, (string) $address, 2);

        return [
            current($address),
            isset($address[1]) ? str_replace(["\r\n", "\r", "\n"], ' ', last($address)) : '',
        ];
    }
}
