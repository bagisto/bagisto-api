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

        $params = $this->normalizeTypeOptions($this->mergeParams($data, $context));

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

        if ($product->type !== 'grouped') {
            try {
                $saleable = (bool) $product->getTypeInstance()->isSaleable();
            } catch (\Throwable) {
                $saleable = true;
            }

            if (! $saleable) {
                throw new InvalidInputException(__('bagistoapi::app.admin.cart.product-not-saleable'), 400);
            }
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
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return AdminCartPresenter::present(Cart::getCart() ?: $cart, false, $e->getMessage() ?: __('bagistoapi::app.admin.cart.item-add-failed'));
        }
    }

    /**
     * Translate the GraphQL-friendly typed option fields into the snake_case map
     * shape Cart::addProduct expects. REST callers that already send the native
     * keys (selected_configurable_option / qty / bundle_options / links) are left
     * untouched — those win when present.
     */
    protected function normalizeTypeOptions(array $params): array
    {
        // configurable — variant product id
        if (! isset($params['selected_configurable_option']) && ! empty($params['selectedConfigurableOption'])) {
            $params['selected_configurable_option'] = (int) $params['selectedConfigurableOption'];
        }

        // downloadable — links already a flat list of ids (camelCase == snake_case key)

        // grouped — [{ productId, quantity }] -> qty { productId: quantity }
        if (! isset($params['qty']) && ! empty($params['groupedQuantities']) && is_array($params['groupedQuantities'])) {
            $qty = [];
            foreach ($params['groupedQuantities'] as $row) {
                $row = (array) $row;
                $pid = (int) ($row['productId'] ?? $row['product_id'] ?? 0);
                if ($pid > 0) {
                    $qty[$pid] = (int) ($row['quantity'] ?? $row['qty'] ?? 0);
                }
            }
            if ($qty) {
                $params['qty'] = $qty;
            }
        }

        // bundle — [{ optionId, productIds, quantity }]
        //   -> bundle_options { optionId: [ids] } + bundle_option_qty { optionId: qty }
        if (! isset($params['bundle_options']) && ! empty($params['bundleOptions']) && is_array($params['bundleOptions'])) {
            $options = [];
            $optionQty = [];
            foreach ($params['bundleOptions'] as $row) {
                $row = (array) $row;
                $optionId = (int) ($row['optionId'] ?? $row['option_id'] ?? 0);
                $ids = array_values(array_filter(array_map('intval', (array) ($row['productIds'] ?? $row['product_ids'] ?? []))));
                if ($optionId > 0 && $ids) {
                    $options[$optionId] = $ids;
                    if (isset($row['quantity']) || isset($row['qty'])) {
                        $optionQty[$optionId] = (int) ($row['quantity'] ?? $row['qty']);
                    }
                }
            }
            if ($options) {
                $params['bundle_options'] = $options;
                if ($optionQty) {
                    $params['bundle_option_qty'] = $optionQty;
                }
            }
        }

        return $params;
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
