<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminBookingCalendar;
use Webkul\BagistoApi\Admin\State\AdminBookingCalendarProvider;

class AdminBookingCalendarResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminBookingCalendarProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminBookingCalendar
    {
        return $this->provider->calendar($context['args']['start'] ?? null, $context['args']['end'] ?? null);
    }
}
