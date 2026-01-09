<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use Webkul\Core\Models\CountryStateTranslation as BaseCountryStateTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class CountryStateTranslation extends BaseCountryStateTranslation {}
