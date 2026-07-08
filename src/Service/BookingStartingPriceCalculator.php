<?php

namespace Webkul\BagistoApi\Service;

use Webkul\BookingProduct\Helpers\Booking as BookingHelper;
use Webkul\Product\Models\Product;

/**
 * Booking "starting from" price = base price + cheapest bookable extra (event
 * ticket / rental unit rate). Mirrors core Booking::getPriceHtml(). Shared by
 * the REST DTO and the GraphQL Eloquent BookingProduct resource.
 */
class BookingStartingPriceCalculator
{
    /**
     * @return array{regular:float,final:float,formattedRegular:string,formattedFinal:string}|null
     */
    public static function compute($bp): ?array
    {
        $extras = self::cheapestBookingExtras($bp);

        if ($extras === null) {
            return null;
        }

        $product = Product::find($bp->product_id);

        if (! $product) {
            return null;
        }

        $type = $product->getTypeInstance();

        $regularFrom = (float) $type->getRegularMinimalPrice() + (float) $extras['regular'];
        $finalFrom = (float) $type->getMinimalPrice() + (float) $extras['final'];

        return [
            'regular' => $regularFrom,
            'final' => $finalFrom,
            'formattedRegular' => core()->currency($regularFrom),
            'formattedFinal' => core()->currency($finalFrom),
        ];
    }

    /**
     * Smallest additional amount charged on top of the base price, split into
     * regular (pre-discount) and final (post-discount). Mirrors core
     * `Booking::getCheapestBookingExtras()`:
     *   - event  → cheapest ticket (final is sale-aware via the core EventTicket helper)
     *   - rental → minimum unit rate (hourly or daily, whichever is set and smallest)
     *   - other  → null
     *
     * @return array{regular:float,final:float}|null
     */
    private static function cheapestBookingExtras($bp): ?array
    {
        if ($bp->type === 'event') {
            $tickets = $bp->event_tickets ?? null;

            if (! $tickets || ! $tickets->count()) {
                return null;
            }

            $helper = app(app(BookingHelper::class)->getTypeHelper('event'));

            $cheapestRegular = null;
            $cheapestFinal = null;

            foreach ($tickets as $ticket) {
                $regular = (float) $ticket->price;

                $final = $helper->isInSale($ticket)
                    ? (float) $ticket->special_price
                    : $regular;

                if ($cheapestRegular === null || $regular < $cheapestRegular) {
                    $cheapestRegular = $regular;
                }

                if ($cheapestFinal === null || $final < $cheapestFinal) {
                    $cheapestFinal = $final;
                }
            }

            return ['regular' => $cheapestRegular, 'final' => $cheapestFinal];
        }

        if ($bp->type === 'rental') {
            $slot = $bp->rental_slot ?? null;

            if (! $slot) {
                return null;
            }

            $rates = array_filter([
                (float) $slot->hourly_price,
                (float) $slot->daily_price,
            ], fn ($rate) => $rate > 0);

            if (! $rates) {
                return null;
            }

            $min = min($rates);

            return ['regular' => $min, 'final' => $min];
        }

        return null;
    }
}
