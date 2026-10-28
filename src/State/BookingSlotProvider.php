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

class BookingSlotProvider implements ProviderInterface
{
    /**
     * Booking type to helper mapping.
     */
    protected array $bookingHelpers = [];

    /**
     * Create a new instance.
     */
    public function __construct(
        protected BookingProductRepository $bookingProductRepository,
        protected DefaultSlotHelper $defaultSlotHelper,
        protected AppointmentSlotHelper $appointmentSlotHelper,
        protected RentalSlotHelper $rentalSlotHelper,
        protected EventTicketHelper $eventTicketHelper,
        protected TableSlotHelper $tableSlotHelper
    ) {
        $this->bookingHelpers = [
            'default' => $this->defaultSlotHelper,
            'appointment' => $this->appointmentSlotHelper,
            'rental' => $this->rentalSlotHelper,
            'event' => $this->eventTicketHelper,
            'table' => $this->tableSlotHelper,
        ];
    }

    /**
     * Provide booking slots for GraphQL query.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?array {
        $args = $context['args'] ?? [];

        $id = $args['id'] ?? request()->query('id');
        $date = $args['date'] ?? request()->query('date');

        if ($id === null) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.booking-slot.id-required')
            );
        }

        if ($date === null) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.booking-slot.date-required')
            );
        }

        $bookingProduct = $this->bookingProductRepository->find($id);

        if (! $bookingProduct) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.booking-slot.product-not-found')
            );
        }

        if (! isset($this->bookingHelpers[$bookingProduct->type])) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.booking-slot.invalid-type')
            );
        }

        $slots = $this->bookingHelpers[$bookingProduct->type]->getSlotsByDate($bookingProduct, $date);

        $identifierPrefix = $bookingProduct->id.'-'.$date.'-';

        if ($bookingProduct->type === 'rental') {
            return $this->buildRentalSlots($slots, $identifierPrefix);
        }

        return $this->buildFlatSlots($slots, $identifierPrefix);
    }

    /**
     * Build grouped slot response for rental hourly booking type.
     * Each BookingSlot represents an admin-configured time range group.
     */
    private function buildRentalSlots(array $rawSlots, string $identifierPrefix): array
    {
        $result = [];
        $index = 1;

        foreach ($rawSlots as $group) {
            if (
                ! isset($group['slots'])
                || empty($group['slots'])
            ) {
                continue;
            }

            $subSlots = [];

            foreach ($group['slots'] as $slot) {
                $subSlots[] = [
                    'from' => $slot['from'] ?? null,
                    'to' => $slot['to'] ?? null,
                    'timestamp' => $this->buildRentalTimestamp($slot),
                    'qty' => isset($slot['qty']) ? (string) $slot['qty'] : null,
                ];
            }

            $slotId = (string) $index++;

            $result[] = new BookingSlot(
                slotId: $slotId,
                time: $group['time'] ?? null,
                slots: $subSlots,
                identifier: $identifierPrefix.$slotId,
            );
        }

        return $result;
    }

    /**
     * Build flat slot response for default, appointment, table, and event types.
     * Each BookingSlot is a single time slot with from/to/timestamp/qty.
     */
    private function buildFlatSlots(array $rawSlots, string $identifierPrefix): array
    {
        $result = [];
        $index = 1;

        foreach ($rawSlots as $slot) {
            $slotId = $slot['timestamp'] ?? (string) $index++;

            $result[] = new BookingSlot(
                slotId: $slotId,
                identifier: $identifierPrefix.$slotId,
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
