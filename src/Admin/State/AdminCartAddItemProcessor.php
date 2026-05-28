<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;

/**
 * POST /api/admin/carts/{id}/items — add a product to the draft cart.
 *
 * Mirrors the monolith CartController::storeItem: validate product, call
 * Cart::addProduct($product, $params) using the full request body so every
 * product-type-specific key is forwarded unchanged.
 */
class AdminCartAddItemProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCart
    {
        $cart = AdminCartGuard::resolve(AdminCartGuard::resolveId($uriVariables, $context));

        $params = $this->mergeParams($data, $context);

        $productId = (int) ($params['product_id'] ?? $params['productId'] ?? 0);

        if ($productId <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.product-required'));
        }

        $product = app(ProductRepository::class)->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.product-not-found'));
        }

        if ($product->type === 'booking') {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.booking-unsupported'));
        }

        $params['product_id'] = $productId;
        if (isset($params['quantity'])) {
            $params['quantity'] = (int) $params['quantity'];
        }

        Cart::setCart($cart);

        try {
            $result = Cart::addProduct($product, $params);

            if (is_array($result) && isset($result['warning'])) {
                return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, (string) $result['warning']);
            }

            Cart::collectTotals();

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, true, __('bagistoapi::app.admin.cart.item-added'));
        } catch (\Throwable $e) {
            Log::warning('AdminCart addItem failed', [
                'cart_id'    => $cart->id,
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.item-add-failed'));
        }
    }

    protected function mergeParams(mixed $data, array $context): array
    {
        $params = request()->all();

        if (! empty($context['args']['input']) && is_array($context['args']['input'])) {
            foreach ($context['args']['input'] as $k => $v) {
                if ($v !== null) {
                    $params[$k] = $v;
                }
            }
        }

        if (is_object($data)) {
            foreach (get_object_vars($data) as $k => $v) {
                if ($v !== null && $k !== 'cartId') {
                    $params[$k] = $v;
                }
            }
        }

        return $params;
    }
}
