<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\BookingSlot;
use Webkul\BookingProduct\Helpers\AppointmentSlot as AppointmentSlotHelper;
use Webkul\BookingProduct\Helpers\DefaultSlot as DefaultSlotHelper;
use Webkul\BookingProduct\Helpers\EventTicket as EventTicketHelper;
use Webkul\BookingProduct\Helpers\RentalSlot as RentalSlotHelper;
use Webkul\BookingProduct\Helpers\TableSlot as TableSlotHelper;
use Webkul\BookingProduct\Repositories\BookingProductRepository;

/**
 * Provider for fetching booking slots via GraphQL
 *
 * Handles the bookingSlot query that accepts:
 * - id: The booking product ID
 * - date: The date for which to fetch available slots
 */
class BookingSlotProvider implements ProviderInterface
{
    /**
     * Booking type to helper mapping
     */
    protected array $bookingHelpers = [];

    public function __construct(
        protected BookingProductRepository $bookingProductRepository,
        protected DefaultSlotHelper $defaultSlotHelper,
        protected AppointmentSlotHelper $appointmentSlotHelper,
        protected RentalSlotHelper $rentalSlotHelper,
        protected EventTicketHelper $eventTicketHelper,
        protected TableSlotHelper $tableSlotHelper
    ) {
        $this->bookingHelpers = [
            'default'     => $this->defaultSlotHelper,
            'appointment' => $this->appointmentSlotHelper,
            'rental'      => $this->rentalSlotHelper,
            'event'       => $this->eventTicketHelper,
            'table'       => $this->tableSlotHelper,
        ];
    }

    /**
     * Provide booking slots for GraphQL query
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?array {
        // Get arguments from GraphQL query
        $args = $context['args'] ?? [];

        $id = $args['id'] ?? null;
        $date = $args['date'] ?? null;

        if ($id === null) {
            throw new BadRequestHttpException(
                'bagistoapi::app.graphql.booking-slot.id-required'
            );
        }

        if ($date === null) {
            throw new BadRequestHttpException(
                'bagistoapi::app.graphql.booking-slot.date-required'
            );
        }

        // Find the booking product
        $bookingProduct = $this->bookingProductRepository->find($id);

        if (! $bookingProduct) {
            throw new BadRequestHttpException(
                'bagistoapi::app.graphql.booking-slot.product-not-found'
            );
        }

        // Check if the booking type has a helper
        if (! isset($this->bookingHelpers[$bookingProduct->type])) {
            throw new BadRequestHttpException(
                'bagistoapi::app.graphql.booking-slot.invalid-type'
            );
        }

        // Get slots for the given date using the appropriate helper
        $slots = $this->bookingHelpers[$bookingProduct->type]->getSlotsByDate($bookingProduct, $date);

        if ($bookingProduct->type === 'rental') {
            return $this->buildRentalSlots($slots);
        }

        return $this->buildFlatSlots($slots);
    }

    /**
     * Build grouped slot response for rental hourly booking type.
     *
     * Each BookingSlot represents an admin-configured time range group
     * (e.g., "10:00 AM - 12:00 PM") with a nested `slots` array
     * containing the individual hourly sub-slots.
     *
     * Raw helper structure:
     * [
     *   ['time' => '10:00 AM - 12:00 PM', 'slots' => [['from', 'to', 'from_timestamp', 'to_timestamp', 'qty'], ...]],
     *   ['time' => '12:00 PM - 09:00 PM', 'slots' => [...]],
     * ]
     */
    private function buildRentalSlots(array $rawSlots): array
    {
        $result = [];
        $index = 1;

        foreach ($rawSlots as $group) {
            if (! isset($group['slots']) || empty($group['slots'])) {
                continue;
            }

            $subSlots = [];

            foreach ($group['slots'] as $slot) {
                $subSlots[] = [
                    'from'      => $slot['from'] ?? null,
                    'to'        => $slot['to'] ?? null,
                    'timestamp' => $this->buildRentalTimestamp($slot),
                    'qty'       => isset($slot['qty']) ? (string) $slot['qty'] : null,
                ];
            }

            $result[] = new BookingSlot(
                slotId: (string) $index++,
                time: $group['time'] ?? null,
                slots: $subSlots,
            );
        }

        return $result;
    }

    /**
     * Build flat slot response for default, appointment, table, and event types.
     *
     * Each BookingSlot is a single time slot with from/to/timestamp/qty.
     */
    private function buildFlatSlots(array $rawSlots): array
    {
        $result = [];
        $index = 1;

        foreach ($rawSlots as $slot) {
            $result[] = new BookingSlot(
                slotId: $slot['timestamp'] ?? (string) $index++,
                from: $slot['from'] ?? null,
                to: $slot['to'] ?? null,
                timestamp: $slot['timestamp'] ?? null,
                qty: isset($slot['qty']) ? (string) $slot['qty'] : null,
            );
        }

        return $result;
    }

    /**
     * Build "from_timestamp-to_timestamp" string for rental slots.
     */
    private function buildRentalTimestamp(array $slot): ?string
    {
        $from = $slot['from_timestamp'] ?? '';
        $to = $slot['to_timestamp'] ?? '';

        $timestamp = $from.'-'.$to;

        return $timestamp !== '-' ? $timestamp : null;
    }
}
