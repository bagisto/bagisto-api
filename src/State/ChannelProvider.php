<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Channel;

class ChannelProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($uriVariables['id'])) {
            return Channel::with(['locales', 'currencies', 'default_locale', 'base_currency'])
                ->find($uriVariables['id']);
        }

        $query = Channel::with(['locales', 'currencies', 'default_locale', 'base_currency'])
            ->orderBy('id', 'asc');

        $args = $context['args'] ?? [];
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;

        if ($first !== null || $last !== null) {
            return new Paginator($query->paginate($first ?? $last));
        }

        // REST: the Pagination service reads page/itemsPerPage from $context['filters'],
        // which ReadProvider normally populates — but we merge request()->query() to be
        // resilient to context variations.
        $context['filters'] = array_merge($context['filters'] ?? [], request()->query());
        $perPage = $this->pagination->getLimit($operation, $context);
        $page = $this->pagination->getPage($context);

        return new Paginator($query->paginate($perPage, ['*'], 'page', $page));
    }
}
