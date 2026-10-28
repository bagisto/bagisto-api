<?php

declare(strict_types=1);

namespace Webkul\BagistoApi\Http\Controllers;

use ApiPlatform\Laravel\GraphQl\Controller\EntrypointController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminGraphQLEntrypointController
{
    /**
     * Create a new instance.
     */
    public function __construct(private readonly EntrypointController $entrypoint) {}

    /**
     * Handle the invoke magic method.
     */
    public function __invoke(Request $request): Response
    {
        return ($this->entrypoint)($request);
    }
}
