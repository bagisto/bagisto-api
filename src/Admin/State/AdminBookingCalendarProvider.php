<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Models\AdminBookingCalendar;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BookingProduct\Repositories\BookingRepository;

/**
 * Bookings in a date window, mirroring the admin BookingController::get calendar branch.
 */
class AdminBookingCalendarProvider implements ProviderInterface
{
    public function __construct(protected BookingRepository $bookingRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->calendar(request()->query('start'), request()->query('end'))];
    }

    public function calendar(mixed $start, mixed $end): AdminBookingCalendar
    {
        AdminReadPermissions::authorize(null, AdminBookingCalendar::class, 'sales.bookings');

        $validator = Validator::make(['start' => $start, 'end' => $end], [
            'start' => 'nullable|date_format:Y-m-d',
            'end' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.booking.calendar-date-invalid'), 422);
        }

        $startDate = $start
            ? Carbon::createFromTimeString($start.' 00:00:01')
            : Carbon::now()->startOfWeek();

        $endDate = $end
            ? Carbon::createFromTimeString($end.' 23:59:59')
            : Carbon::now()->endOfWeek();

        $result = new AdminBookingCalendar;
        $result->start = $startDate->format('Y-m-d');
        $result->end = $endDate->format('Y-m-d');
        $result->bookings = $this->bookingRepository
            ->getBookings([$startDate->timestamp, $endDate->timestamp])
            ->map(fn ($booking) => $this->mapBooking($booking))
            ->values()
            ->all();

        return $result;
    }

    /**
     * Shape one calendar row the way core's calendar response does, with camelCase keys.
     */
    protected function mapBooking(object $booking): array
    {
        $additional = is_string($booking->item_additional)
            ? json_decode($booking->item_additional, true)
            : ($booking->item_additional ?? []);

        return [
            'id' => (int) $booking->id,
            'orderId' => $booking->order_id !== null ? (int) $booking->order_id : null,
            'start' => $this->formatSlot($booking->start),
            'end' => $this->formatSlot($booking->end),
            'status' => $booking->status,
            'email' => $booking->email,
            'fullName' => $booking->full_name,
            'createdAt' => $booking->created_at !== null ? (string) $booking->created_at : null,
            'productSku' => $booking->product_sku,
            'productName' => $booking->product_name,
            'address' => $booking->address,
            'contact' => $booking->contact,
            'city' => $booking->city,
            'state' => $booking->state,
            'country' => $booking->country,
            'postcode' => $booking->postcode,
            'attributes' => $additional['attributes'] ?? [],
        ];
    }

    protected function formatSlot(mixed $timestamp): ?string
    {
        if ($timestamp === null || $timestamp === '') {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp)
            ->timezone(config('app.timezone'))
            ->format('Y-m-d h:i A');
    }
}
