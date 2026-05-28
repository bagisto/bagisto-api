<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;

/**
 * POST /api/admin/carts/{id}/addresses — save billing & shipping addresses.
 *
 * Mirrors CartController::storeAddress: validate, Cart::saveAddresses($params),
 * Cart::collectTotals(). Returns the refreshed cart so the caller can render
 * the shipping-method picker.
 */
class AdminCartSaveAddressProcessor implements ProcessorInterface
{
    /** Address fields the monolith CartAddressRequest requires. */
    protected const REQUIRED_FIELDS = ['firstName', 'lastName', 'email', 'city', 'country', 'state', 'postcode', 'phone'];

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        $params = $this->mergeParams($data, $context);

        $billing = $params['billing'] ?? null;

        if (! is_array($billing) || empty($billing)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.address-required'));
        }

        $params['billing'] = $this->normaliseAddress($billing);
        if (! ($params['billing']['use_for_shipping'] ?? false) && isset($params['shipping']) && is_array($params['shipping'])) {
            $params['shipping'] = $this->normaliseAddress($params['shipping']);
        }

        Cart::setCart($cart);

        if (Cart::hasError()) {
            return AdminCartPresenter::present($cart, false, implode(': ', Cart::getErrors()) ?: __('bagistoapi::app.admin.cart.unknown-error'));
        }

        try {
            Cart::saveAddresses($params);
            Cart::collectTotals();

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.address-saved'));
        } catch (\Throwable $e) {
            Log::warning('AdminCart saveAddress failed', [
                'cart_id' => $cart->id,
                'error'   => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.unknown-error'));
        }
    }

    protected function mergeParams(mixed $data, array $context): array
    {
        $params = request()->all();

        if (! empty($context['args']['input']) && is_array($context['args']['input'])) {
            foreach ($context['args']['input'] as $k => $v) {
                if ($v !== null && $k !== 'cartId') {
                    $params[$k] = $v;
                }
            }
        }

        if (is_object($data)) {
            foreach (['billing', 'shipping'] as $key) {
                if (! empty($data->{$key})) {
                    $params[$key] = $data->{$key};
                }
            }
        }

        return $params;
    }

    /**
     * Convert camelCase address keys to snake_case for the cart facade.
     */
    protected function normaliseAddress(array $address): array
    {
        $map = [
            'firstName'      => 'first_name',
            'lastName'       => 'last_name',
            'companyName'    => 'company_name',
            'useForShipping' => 'use_for_shipping',
            'addressId'      => 'address_id',
        ];

        foreach ($map as $camel => $snake) {
            if (array_key_exists($camel, $address) && ! array_key_exists($snake, $address)) {
                $address[$snake] = $address[$camel];
                unset($address[$camel]);
            }
        }

        if (isset($address['address']) && is_string($address['address'])) {
            $address['address'] = [$address['address']];
        }

        return $address;
    }
}
