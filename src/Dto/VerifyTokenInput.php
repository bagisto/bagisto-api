<?php

namespace Webkul\BagistoApi\Dto;

/**
 * DTO for verifying customer token
 * This is used in GraphQL mutations to validate token and get customer details
 * Token is passed via Authorization: Bearer header, NOT as input parameter.
 *
 * NOTE: Token is NOT a DTO property. It is extracted from the Authorization header
 * via TokenHeaderFacade::getAuthorizationBearerToken() in the processor.
 */
class VerifyTokenInput {}
