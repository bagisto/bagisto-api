<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Product\Models\Product;
use Webkul\User\Models\Admin;

/**
 * DIAGNOSTIC — drives the admin Create-Order flow (draft cart -> add item ->
 * addresses -> shipping -> payment -> place order) for EVERY product type, over
 * BOTH REST and GraphQL, and prints a pass/fail matrix to STDERR.
 *
 * The goal is to discover which product types fail to add-to-cart / order at
 * the API end. It asserts nothing strict so every type runs to completion.
 *
 * Run: php artisan test packages/Webkul/BagistoApi/tests/Feature/Admin/RestApi/CreateOrderProductTypesTest.php
 */
class CreateOrderProductTypesTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Representative saleable product id per type in the dev DB. */
    protected function samples(): array
    {
        return [
            'simple'       => 1,
            'virtual'      => 2505,
            'downloadable' => 2506,
            'grouped'      => 2516,
            'configurable' => 123,
            'bundle'       => 2517,
            'booking'      => 2507,
        ];
    }

    /** Types that carry no stockable items, so no shipping step is needed. */
    protected function isVirtualType(string $type): bool
    {
        return in_array($type, ['virtual', 'downloadable'], true);
    }

    public function test_create_order_every_product_type_matrix(): void
    {
        $admin = $this->createAdmin();

        $lines = [];
        $lines[] = sprintf('%-13s | %-8s | %-26s | %s', 'TYPE', 'TRANSPORT', 'ADD-TO-CART', 'PLACE-ORDER');
        $lines[] = str_repeat('-', 92);

        $collected = [];

        foreach ($this->samples() as $type => $pid) {
            $product = $this->loadProduct($pid);

            if (! $product) {
                $lines[] = sprintf('%-13s | %-8s | %s', $type, 'BOTH', 'SKIP: sample product not found in DB');

                continue;
            }

            $rest = $this->runRest($admin, $type, $product);
            $lines[] = sprintf('%-13s | %-8s | %-26s | %s', $type, 'REST', $rest['add'], $rest['place']);

            $graph = $this->runGraphQL($admin, $type, $product);
            $lines[] = sprintf('%-13s | %-8s | %-26s | %s', $type, 'GraphQL', $graph['add'], $graph['place']);

            $collected[$type] = ['REST' => $rest, 'GraphQL' => $graph];
        }

        fwrite(STDERR, "\n\n===== CREATE-ORDER PRODUCT-TYPE MATRIX =====\n".implode("\n", $lines)."\n=============================================\n\n");

        foreach ($collected as $type => $byTransport) {
            if ($type === 'booking') {
                // Booking is intentionally blocked in admin Create-Order.
                expect($byTransport['REST']['add'])->not->toStartWith('OK');

                continue;
            }

            // Every orderable type must add to cart over BOTH transports
            // (skip only when the dev DB couldn't supply a saleable sample).
            foreach (['REST', 'GraphQL'] as $transport) {
                $add = $byTransport[$transport]['add'];
                if (str_starts_with($add, 'SKIP')) {
                    continue;
                }
                expect($add)->toStartWith('OK');
            }
        }
    }

    protected function loadProduct(int $id): ?Product
    {
        return Product::with([
            'variants',
            'bundle_options.bundle_option_products.product',
            'grouped_products.associated_product',
            'downloadable_links',
        ])->find($id);
    }

    /**
     * Build the type-specific add-to-cart body (snake_case keys, mirroring the
     * storefront), or null if a required sub-selection couldn't be resolved.
     */
    protected function buildPayload(Product $product): ?array
    {
        $base = [
            'product_id' => $product->id,
            'productId'  => $product->id,
            'quantity'   => 1,
        ];

        switch ($product->type) {
            case 'configurable':
                $variant = $product->variants->first(fn ($v) => $this->saleable($v));
                if (! $variant) {
                    return null;
                }
                $base['selected_configurable_option'] = $variant->id;
                break;

            case 'bundle':
                $options = [];
                $qty = [];
                foreach ($product->bundle_options as $option) {
                    if (! $option->is_required) {
                        continue;
                    }
                    $op = $option->bundle_option_products
                        ->sortByDesc('is_default')
                        ->first(fn ($x) => $x->product && $this->saleable($x->product));
                    if ($op) {
                        $options[$option->id] = [$op->id];
                        $qty[$option->id] = $op->qty ?: 1;
                    }
                }
                if (empty($options)) {
                    return null;
                }
                $base['bundle_options'] = $options;
                $base['bundle_option_qty'] = $qty;
                break;

            case 'grouped':
                $map = [];
                foreach ($product->grouped_products as $gp) {
                    if ($gp->associated_product && $this->saleable($gp->associated_product)) {
                        $map[$gp->associated_product_id] = 1;
                    }
                }
                if (empty($map)) {
                    return null;
                }
                $base['qty'] = $map;
                break;

            case 'downloadable':
                $links = $product->downloadable_links->pluck('id')->all();
                if (empty($links)) {
                    return null;
                }
                $base['links'] = $links;
                break;
        }

        return $base;
    }

    /**
     * Build the GraphQL addItemAdminCart input (typed fields) per product type,
     * or null if a required sub-selection can't be resolved.
     */
    protected function buildGraphQLInput(Product $product): ?array
    {
        $input = ['productId' => $product->id, 'quantity' => 1];

        switch ($product->type) {
            case 'configurable':
                $variant = $product->variants->first(fn ($v) => $this->saleable($v));
                if (! $variant) {
                    return null;
                }
                $input['selectedConfigurableOption'] = $variant->id;
                break;

            case 'bundle':
                $options = [];
                foreach ($product->bundle_options as $option) {
                    if (! $option->is_required) {
                        continue;
                    }
                    $op = $option->bundle_option_products
                        ->sortByDesc('is_default')
                        ->first(fn ($x) => $x->product && $this->saleable($x->product));
                    if ($op) {
                        $options[] = ['optionId' => $option->id, 'productIds' => [$op->id], 'quantity' => $op->qty ?: 1];
                    }
                }
                if (empty($options)) {
                    return null;
                }
                $input['bundleOptions'] = $options;
                break;

            case 'grouped':
                $list = [];
                foreach ($product->grouped_products as $gp) {
                    if ($gp->associated_product && $this->saleable($gp->associated_product)) {
                        $list[] = ['productId' => $gp->associated_product_id, 'quantity' => 1];
                    }
                }
                if (empty($list)) {
                    return null;
                }
                $input['groupedQuantities'] = $list;
                break;

            case 'downloadable':
                $links = $product->downloadable_links->pluck('id')->all();
                if (empty($links)) {
                    return null;
                }
                $input['links'] = $links;
                break;
        }

        return $input;
    }

    protected function saleable($product): bool
    {
        try {
            return (bool) $product->getTypeInstance()->isSaleable();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{add: string, place: string}
     */
    protected function runRest(Admin $admin, string $type, Product $product): array
    {
        $customerId = $this->findOrCreateCustomer()->id;

        $cartResp = $this->adminPost($admin, '/api/admin/customers/'.$customerId.'/draft-carts');
        $cartId = $cartResp->json('cartId');
        if (! $cartId) {
            return ['add' => 'SKIP: no draft cart', 'place' => '-'];
        }

        $payload = $this->buildPayload($product);
        if ($payload === null) {
            return ['add' => 'SKIP: no saleable sub-option', 'place' => '-'];
        }

        $addResp = $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/items', $payload);
        $added = $addResp->getStatusCode() < 300 && $addResp->json('success') === true;

        if (! $added) {
            return ['add' => 'FAIL: '.$this->short($addResp->json('message') ?? ('HTTP '.$addResp->getStatusCode())), 'place' => '-'];
        }

        $place = $this->finishAndPlace($admin, $type, $cartId);

        return ['add' => 'OK', 'place' => $place];
    }

    /**
     * @return array{add: string, place: string}
     */
    protected function runGraphQL(Admin $admin, string $type, Product $product): array
    {
        $customerId = $this->findOrCreateCustomer()->id;

        // Bootstrap the draft cart over REST (the GraphQL focus here is add-to-cart).
        $cartId = $this->adminPost($admin, '/api/admin/customers/'.$customerId.'/draft-carts')->json('cartId');
        if (! $cartId) {
            return ['add' => 'SKIP: no draft cart', 'place' => '-'];
        }

        $input = $this->buildGraphQLInput($product);
        if ($input === null) {
            return ['add' => 'SKIP: no saleable sub-option', 'place' => '-'];
        }
        $input['id'] = '/api/admin/carts/'.$cartId;
        $input['cartId'] = (string) $cartId;

        $mutation = <<<'GQL'
            mutation addItem($input: addItemAdminCartInput!) {
              addItemAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $this->adminGraphQL($mutation, ['input' => $input], $admin);

        // Verify via REST whether the item actually landed in the cart.
        $cart = $this->adminGet($admin, '/api/admin/carts/'.$cartId);
        $itemCount = is_array($cart->json('items')) ? count($cart->json('items')) : 0;

        if ($itemCount < 1) {
            $needsOptions = in_array($type, ['configurable', 'bundle', 'grouped', 'downloadable'], true);

            return [
                'add'   => $needsOptions ? 'FAIL: type options not sendable' : 'FAIL: not added',
                'place' => '-',
            ];
        }

        $place = $this->finishAndPlace($admin, $type, $cartId);

        return ['add' => 'OK', 'place' => $place];
    }

    /**
     * Save addresses -> (shipping) -> payment -> place. Returns a short status.
     */
    protected function finishAndPlace(Admin $admin, string $type, $cartId): string
    {
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/addresses', [
            'billing' => [
                'firstName'      => 'Jane',
                'lastName'       => 'Doe',
                'email'          => 'jane@example.com',
                'address'        => ['12 Main St'],
                'city'           => 'New York',
                'country'        => 'US',
                'state'          => 'NY',
                'postcode'       => '10001',
                'phone'          => '+12025550000',
                'useForShipping' => true,
            ],
        ]);

        if (! $this->isVirtualType($type)) {
            $rates = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/shipping-methods');
            $method = $this->firstShippingMethod($rates->json('data'));
            if (! $method) {
                return 'BLOCKED: no shipping rate';
            }
            $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/shipping-methods', ['shippingMethod' => $method]);
        }

        $payments = $this->adminGet($admin, '/api/admin/carts/'.$cartId.'/payment-methods');
        $payment = $this->firstSupportedPayment($payments->json('data'));
        if (! $payment) {
            return 'BLOCKED: no COD/money-transfer';
        }
        $this->adminPost($admin, '/api/admin/carts/'.$cartId.'/payment-methods', ['method' => $payment]);

        $placeResp = $this->adminPost($admin, '/api/admin/orders/place/'.$cartId);
        $code = $placeResp->getStatusCode();

        if ($code === 201 && $placeResp->json('orderId')) {
            return 'OK (order #'.$placeResp->json('incrementId').')';
        }

        return 'FAIL ('.$code.'): '.$this->short($placeResp->json('message') ?? '');
    }

    protected function firstShippingMethod(?array $rates): ?string
    {
        foreach ((array) $rates as $rate) {
            $m = $rate['method'] ?? $rate['code'] ?? null;
            if ($m) {
                return $m;
            }
        }

        return null;
    }

    protected function firstSupportedPayment(?array $methods): ?string
    {
        $codes = collect((array) $methods)->pluck('method')->filter()->all();
        foreach (['cashondelivery', 'moneytransfer'] as $pref) {
            if (in_array($pref, $codes, true)) {
                return $pref;
            }
        }

        return null;
    }

    protected function short(?string $msg): string
    {
        $msg = trim((string) $msg);

        return mb_strlen($msg) > 60 ? mb_substr($msg, 0, 57).'...' : $msg;
    }
}
