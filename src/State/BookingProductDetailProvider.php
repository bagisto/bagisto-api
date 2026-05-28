<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Dto\ProductDetail\BookingProductDto;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\BookingProduct;

/**
 * Provider for /api/shop/booking-products/{id}.
 *
 * Loads a BookingProduct with its type-specific slot relations and returns
 * a fully-embedded BookingProductDto so clients don't have to traverse
 * dangling slot IRIs. Same DTO shape used by the PDP detail's bookingProducts.
 */
class BookingProductDetailProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): BookingProductDto
    {
        $id = $uriVariables['id'] ?? null;

        if ($id === null || $id === '') {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        $bp = BookingProduct::with([
            'appointment_slot',
            'default_slot',
            'rental_slot',
            'table_slot',
            'event_tickets',
        ])->find($id);

        if (! $bp) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        return BookingProductDto::fromModel($bp);
    }
}
