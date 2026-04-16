<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Channel;

/**
 * ChannelProvider - Retrieves channels with eager-loaded locales and currencies
 */
class ChannelProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Retrieve channels with eager-loaded relationships
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($uriVariables['id'])) {
            // Single channel request with eager loading
            return Channel::with(['locales', 'currencies', 'default_locale', 'base_currency'])
                ->find($uriVariables['id']);
        }

        // Collection request with eager loading
        $query = Channel::with(['locales', 'currencies', 'default_locale', 'base_currency'])
            ->orderBy('id', 'asc');

        // Extract pagination parameters
        $args = $context['args'] ?? [];
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;

        $defaultPerPage = 15;
        $perPage = $first ?? $last ?? $defaultPerPage;

        $laravelPaginator = $query->paginate($perPage);

        return new Paginator($laravelPaginator);
    }
}
