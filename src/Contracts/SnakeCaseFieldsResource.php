<?php

namespace Webkul\BagistoApi\Contracts;

/**
 * Marker for a POPO API resource whose public properties are snake_case (so
 * multi-word fields resolve over a GraphQL query). Such resources need their
 * inner GraphQL mutation field names denormalized to snake_case, the same as
 * Eloquent-backed and Admin resources.
 */
interface SnakeCaseFieldsResource {}
