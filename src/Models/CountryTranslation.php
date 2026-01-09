<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use Webkul\Core\Models\CountryTranslation as BaseCountryTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class CountryTranslation extends BaseCountryTranslation {}
