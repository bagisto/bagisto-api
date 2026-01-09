<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use Webkul\BookingProduct\Models\BookingProduct as BaseBookingProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/booking-products/{id}',
    operations: [],
    graphQlOperations: []
)]
class BookingProduct extends BaseBookingProduct {}
