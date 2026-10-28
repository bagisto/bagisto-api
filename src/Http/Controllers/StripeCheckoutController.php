<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Webkul\Sales\Contracts\Order as OrderContract;
use Webkul\Stripe\Http\Controllers\StripeController;

class StripeCheckoutController extends StripeController
{
    /**
     * Turn a paid Stripe checkout session into an order through core's own settlement, which places it
     * once and returns the order already placed for the cart when the webhook got there first.
     */
    public function settle(object $session): ?OrderContract
    {
        return $this->placeOrder($session);
    }
}
