<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Service\BookingSlotParser;
use Webkul\BookingProduct\Helpers\Booking;
use Webkul\BookingProduct\Models\BookingProduct;

trait HandlesBookingCartItems
{
    /**
     * Normalize booking payload so clients can send user-friendly slot strings like "10:00 AM - 11:00 AM".
     */
    private function normalizeBookingForCart(array $booking, int $productId): array
    {
        if (($booking['type'] ?? null) === 'event') {
            $this->validateEventBookingPayload($booking);

            return $booking;
        }

        if (($booking['type'] ?? null) === 'table') {
            $note = $booking['note'] ?? null;

            if (
                ! is_string($note)
                || trim($note) === ''
            ) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.cart.booking-note-required'));
            }
        }

        if (
            ! isset($booking['slot'])
            || ! is_string($booking['slot'])
        ) {
            return $booking;
        }

        if (! $this->looksLikeFormattedTimeRange($booking['slot'])) {
            return $booking;
        }

        $date = $booking['date'] ?? null;

        if (
            ! is_string($date)
            || $date === ''
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.booking-date-required'));
        }

        $timestamps = (new BookingSlotParser)->parse($date, $booking['slot'], $this->getChannelTimezone());

        if (
            ($booking['type'] ?? null) === 'rental'
            || ($booking['renting_type'] ?? null) === 'hourly'
        ) {
            $booking['renting_type'] ??= 'hourly';
            $booking['slot'] = $timestamps;
        } else {
            $bookingType = $booking['type'] ?? null;
            $duration = $this->getBookingDurationMinutes($productId, is_string($bookingType) ? $bookingType : null);

            if ($duration) {
                $timestamps['to'] = $timestamps['from'] + ($duration * 60);
            }

            $booking['slot'] = $timestamps['from'].'-'.$timestamps['to'];

            $this->validateFormattedSlotExists($productId, $bookingType, $date, $booking['slot']);
        }

        return $booking;
    }

    /**
     * Validate event booking payload.
     */
    private function validateEventBookingPayload(array $booking): void
    {
        $qty = $booking['qty'] ?? null;

        if (
            ! is_array($qty)
            || empty($qty)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.booking-qty-required'));
        }

        $hasAtLeastOne = false;

        foreach ($qty as $ticketId => $count) {
            if (
                $count === null
                || $count === ''
            ) {
                continue;
            }

            if (
                ! is_numeric($count)
                || (int) $count < 0
            ) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.cart.booking-ticket-qty-invalid'));
            }

            if ((int) $count > 0) {
                $hasAtLeastOne = true;
            }
        }

        if (! $hasAtLeastOne) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.booking-ticket-required'));
        }
    }

    /**
     * Get channel timezone.
     */
    private function getChannelTimezone(): ?string
    {
        $channel = core()->getCurrentChannel();

        return $channel?->timezone ?: config('app.timezone');
    }

    /**
     * Get booking duration minutes.
     */
    private function getBookingDurationMinutes(int $productId, ?string $bookingType): ?int
    {
        if (
            ! $bookingType
            || ! in_array($bookingType, ['appointment', 'default', 'table'], true)
        ) {
            return null;
        }

        $bookingProduct = BookingProduct::query()
            ->where('product_id', $productId)
            ->first();

        return match ($bookingType) {
            'appointment' => (int) ($bookingProduct?->appointment_slot?->duration ?? 0),
            'default' => (int) ($bookingProduct?->default_slot?->duration ?? 0),
            'table' => (int) ($bookingProduct?->table_slot?->duration ?? 0),
            default => null,
        } ?: null;
    }

    /**
     * Validate formatted slot exists.
     */
    private function validateFormattedSlotExists(int $productId, mixed $bookingType, string $date, string $slot): void
    {
        if (
            ! is_string($bookingType)
            || ! in_array($bookingType, ['appointment', 'default', 'table'], true)
        ) {
            return;
        }

        $bookingProduct = BookingProduct::query()
            ->where('product_id', $productId)
            ->first();

        if (! $bookingProduct) {
            return;
        }

        $bookingHelper = app(Booking::class);

        $typeHelper = app($bookingHelper->getTypeHelper($bookingType));

        $slots = $typeHelper->getSlotsByDate($bookingProduct, $date);

        $exists = collect($slots)->contains(function ($candidate) use ($slot) {
            return ($candidate['timestamp'] ?? null) === $slot;
        });

        if (! $exists) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.booking-slot-unavailable'));
        }
    }

    /**
     * Looks like formatted time range.
     */
    private function looksLikeFormattedTimeRange(string $value): bool
    {
        $slot = trim($value);

        if ($slot === '') {
            return false;
        }

        $hasSeparator = preg_match('/\\s*[-–—]\\s*/u', $slot) === 1;
        $hasAmPm = preg_match('/\\b(am|pm)\\b/i', $slot) === 1;
        $hasColon = str_contains($slot, ':');

        return $hasSeparator && ($hasColon || $hasAmPm);
    }
}
