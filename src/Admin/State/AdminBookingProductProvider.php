<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Models\AdminBookingProduct;
use Webkul\BagistoApi\Admin\Models\AdminBookingProductSlots;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BookingProduct\Helpers\Booking as BookingHelper;
use Webkul\BookingProduct\Helpers\EventTicket as EventTicketHelper;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Repositories\BookingProductRepository;

/**
 * Booking config and slots for the admin Create Order drawer, mirroring the admin
 * BookingProductController::config and ::slots.
 */
class AdminBookingProductProvider implements ProviderInterface
{
    public function __construct(
        protected BookingProductRepository $bookingProductRepository,
        protected BookingHelper $bookingHelper,
        protected EventTicketHelper $eventTicketHelper,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $productId = (int) ($uriVariables['productId'] ?? request()->route('productId'));

        if ($operation->getClass() === AdminBookingProductSlots::class) {
            return [$this->slots($productId, request()->query('date'))];
        }

        return [$this->config($productId)];
    }

    public function config(int $productId): AdminBookingProduct
    {
        AdminReadPermissions::authorize(null, AdminBookingProduct::class);

        $bookingProduct = $this->findBookingProduct($productId);

        $result = new AdminBookingProduct;
        $result->product_id = $productId;
        $result->booking_product_id = $bookingProduct->id;
        $result->type = $bookingProduct->type;
        $result->available_every_week = (bool) $bookingProduct->available_every_week;
        $result->available_from = $bookingProduct->available_from?->format('Y-m-d');
        $result->available_to = $bookingProduct->available_to?->format('Y-m-d');
        $result->calendar = $this->bookingHelper->getCalendarAvailability($bookingProduct);

        if ($bookingProduct->type === 'rental') {
            $result->rental_slot = [
                'renting_type' => $bookingProduct->rental_slot?->renting_type,
                'daily_price' => $bookingProduct->rental_slot?->daily_price,
                'hourly_price' => $bookingProduct->rental_slot?->hourly_price,
            ];
        }

        if ($bookingProduct->type === 'table') {
            $result->table_slot = [
                'guest_capacity' => $bookingProduct->table_slot?->guest_capacity,
                'guest_limit' => $bookingProduct->table_slot?->guest_limit,
                'price_type' => $bookingProduct->table_slot?->price_type,
            ];
        }

        if ($bookingProduct->type === 'event') {
            $result->event_tickets = collect($this->eventTicketHelper->getTickets($bookingProduct))
                ->map(fn ($ticket) => is_array($ticket) ? $ticket : $ticket->toArray())
                ->values()
                ->all();
        }

        return $result;
    }

    public function slots(int $productId, mixed $date): AdminBookingProductSlots
    {
        AdminReadPermissions::authorize(null, AdminBookingProductSlots::class);

        $bookingProduct = $this->findBookingProduct($productId);

        if (Validator::make(['date' => $date], ['date' => 'required|date_format:Y-m-d'])->fails()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.booking-product.date-invalid'), 422);
        }

        $result = new AdminBookingProductSlots;
        $result->product_id = $productId;
        $result->date = $date;
        $result->slots = array_values($this->bookingHelper->getSlotsByDate($bookingProduct, $date));

        return $result;
    }

    protected function findBookingProduct(int $productId): BookingProduct
    {
        $bookingProduct = $productId > 0
            ? $this->bookingProductRepository->findOneByField('product_id', $productId)
            : null;

        if (! $bookingProduct) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.booking-product.not-found'));
        }

        return $bookingProduct;
    }
}
