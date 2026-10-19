<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Dto\ProductDetail\BookingProductDto;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\BookingProduct;

class BookingProductDetailProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): BookingProductDto
    {
        $id = $uriVariables['id'] ?? null;

        if (
            $id === null
            || $id === ''
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        $bookingProduct = BookingProduct::with([
            'appointment_slot',
            'default_slot',
            'rental_slot',
            'table_slot',
            'event_tickets',
        ])->find($id);

        if (! $bookingProduct) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product.not-found'));
        }

        return BookingProductDto::fromModel($bookingProduct);
    }
}
