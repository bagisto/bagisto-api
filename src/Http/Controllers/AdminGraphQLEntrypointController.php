<?php

declare(strict_types=1);

namespace Webkul\BagistoApi\Http\Controllers;

use ApiPlatform\Laravel\GraphQl\Controller\EntrypointController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin GraphQL entrypoint (`POST /api/admin/graphql`).
 *
 * Delegates to an ADMIN-scoped API Platform EntrypointController instance bound in
 * the service provider under this class name. The scoped instance is built with a
 * SchemaBuilder that only sees admin resources (storefront resources excluded),
 * halving the per-request GraphQL schema-build cost versus the shared full schema.
 *
 * API Platform's own EntrypointController is `final`, so we wrap rather than extend it.
 */
final class AdminGraphQLEntrypointController
{
    public function __construct(private readonly EntrypointController $entrypoint) {}

    public function __invoke(Request $request): Response
    {
        return ($this->entrypoint)($request);
    }
}
