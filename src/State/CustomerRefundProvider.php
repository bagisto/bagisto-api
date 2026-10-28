<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Dto\CustomerOrder\CustomerRefundDto;
use Webkul\BagistoApi\Dto\CustomerOrder\RefundItemDto;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerRefund;
use Webkul\BagistoApi\Models\CustomerRefundItem;

/**
 * Serves the authenticated customer's refunds: the Eloquent model over GraphQL, a flat DTO
 * with embedded items over REST.
 */
class CustomerRefundProvider implements ProviderInterface
{
    private const DEFAULT_PER_PAGE = 10;

    private const MAX_PER_PAGE = 50;

    private const REFUND_FIELDS = [
        'increment_id', 'state', 'base_currency_code', 'channel_currency_code', 'order_currency_code',
        'order_increment_id', 'formatted_sub_total', 'formatted_sub_total_incl_tax', 'formatted_shipping_amount',
        'formatted_shipping_amount_incl_tax', 'formatted_tax_amount', 'formatted_discount_amount',
        'formatted_adjustment_refund', 'formatted_adjustment_fee', 'formatted_grand_total', 'formatted_base_grand_total',
    ];

    private const REFUND_AMOUNTS = [
        'sub_total', 'base_sub_total', 'sub_total_incl_tax', 'base_sub_total_incl_tax',
        'shipping_amount', 'base_shipping_amount', 'shipping_amount_incl_tax', 'base_shipping_amount_incl_tax',
        'shipping_tax_amount', 'base_shipping_tax_amount', 'tax_amount', 'base_tax_amount',
        'discount_percent', 'discount_amount', 'base_discount_amount', 'adjustment_refund', 'base_adjustment_refund',
        'adjustment_fee', 'base_adjustment_fee', 'grand_total', 'base_grand_total',
    ];

    private const ITEM_FIELDS = [
        'sku', 'name', 'formatted_price', 'formatted_price_incl_tax', 'formatted_total',
        'formatted_total_incl_tax', 'formatted_tax_amount', 'formatted_discount_amount',
    ];

    private const ITEM_AMOUNTS = [
        'price', 'base_price', 'price_incl_tax', 'base_price_incl_tax', 'total', 'base_total',
        'total_incl_tax', 'base_total_incl_tax', 'tax_amount', 'base_tax_amount',
        'discount_percent', 'discount_amount', 'base_discount_amount',
    ];

    /**
     * Provide the customer's refunds for collection or single-item operations.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $isGraphQL = ! empty($context['graphql_operation_name']);

        if (! $operation instanceof GetCollection && ! $operation instanceof QueryCollection) {
            $refund = $this->findOwned((int) $customer->id, $uriVariables['id'] ?? null);

            return $isGraphQL ? $refund : $this->toDto($refund);
        }

        return $this->provideCollection((int) $customer->id, $context, $isGraphQL);
    }

    /**
     * A single refund of the customer's orders, or a not-found error.
     */
    private function findOwned(int $customerId, mixed $id): CustomerRefund
    {
        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-refund.id-required'));
        }

        $refund = $this->baseQuery($customerId)->find($id);

        if (! $refund) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-refund.not-found', ['id' => $id]));
        }

        return $refund;
    }

    /**
     * A page of the customer's refunds, newest first, optionally scoped to one order.
     */
    private function provideCollection(int $customerId, array $context, bool $isGraphQL): Paginator
    {
        $args = $context['args'] ?? [];
        $request = request();

        $query = $this->baseQuery($customerId);

        $orderId = $args['orderId']
            ?? $request->query('order_id')
            ?? $request->query('orderId');

        if ($orderId !== null && $orderId !== '') {
            $query->where('refunds.order_id', (int) $orderId);
        }

        [$perPage, $offset] = $isGraphQL ? $this->cursorPaging($args) : $this->pagePaging();

        $query->orderBy('refunds.id', 'desc');

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $refunds = $query->offset($offset)->limit($perPage)->get();

        if (! $isGraphQL) {
            $refunds = $refunds->map(fn (CustomerRefund $refund) => $this->toDto($refund));
        }

        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator($refunds, $total, $perPage, $currentPage, ['path' => $request->url()])
        );
    }

    /**
     * Refunds of the customer's orders with the order increment ID and items preloaded.
     */
    private function baseQuery(int $customerId): Builder
    {
        return CustomerRefund::query()
            ->ownedBy($customerId)
            ->leftJoin('orders', 'orders.id', '=', 'refunds.order_id')
            ->select('refunds.*', 'orders.increment_id as order_increment_id')
            ->with('items');
    }

    /**
     * Page size and offset from GraphQL cursor arguments.
     */
    private function cursorPaging(array $args): array
    {
        $perPage = (int) ($args['first'] ?? $args['last'] ?? self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));
        $offset = 0;

        if ($after = $args['after'] ?? null) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before = $args['before'] ?? null) {
            $decoded = base64_decode($before, true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $perPage);
        }

        return [$perPage, $offset];
    }

    /**
     * Page size and offset from the REST `page` and `per_page` query parameters.
     */
    private function pagePaging(): array
    {
        $perPage = (int) request()->query('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;
        $page = max(1, (int) request()->query('page', 1));

        return [$perPage, ($page - 1) * $perPage];
    }

    /**
     * Map a refund to its REST shape.
     */
    private function toDto(CustomerRefund $refund): CustomerRefundDto
    {
        $dto = new CustomerRefundDto;

        $dto->id = (int) $refund->id;
        $dto->order_id = $refund->order_id !== null ? (int) $refund->order_id : null;
        $dto->total_qty = $refund->total_qty !== null ? (int) $refund->total_qty : null;

        foreach (self::REFUND_FIELDS as $field) {
            $dto->{$field} = $refund->{$field};
        }

        foreach (self::REFUND_AMOUNTS as $field) {
            $dto->{$field} = $refund->{$field} !== null ? (float) $refund->{$field} : null;
        }

        $dto->created_at = $refund->created_at?->toIso8601String();
        $dto->updated_at = $refund->updated_at?->toIso8601String();

        $dto->items = $refund->items
            ->map(fn (CustomerRefundItem $item) => $this->toItemDto($item))
            ->all();

        return $dto;
    }

    /**
     * Map a refunded item to its REST shape.
     */
    private function toItemDto(CustomerRefundItem $item): RefundItemDto
    {
        $dto = new RefundItemDto;

        $dto->id = (int) $item->id;
        $dto->order_item_id = $item->order_item_id !== null ? (int) $item->order_item_id : null;
        $dto->product_id = $item->product_id !== null ? (int) $item->product_id : null;
        $dto->qty = $item->qty !== null ? (int) $item->qty : null;
        $dto->additional = $item->additional;

        foreach (self::ITEM_FIELDS as $field) {
            $dto->{$field} = $item->{$field};
        }

        foreach (self::ITEM_AMOUNTS as $field) {
            $dto->{$field} = $item->{$field} !== null ? (float) $item->{$field} : null;
        }

        return $dto;
    }
}
