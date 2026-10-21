<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Channel;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class ChannelProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($uriVariables['id'])) {
            return Channel::with(['locales', 'currencies', 'default_locale', 'base_currency'])
                ->find($uriVariables['id']);
        }

        $query = Channel::with(['locales', 'currencies', 'default_locale', 'base_currency'])
            ->orderBy('id', 'asc');

        $args = $context['args'] ?? [];

        if (
            isset($args['first'])
            || isset($args['last'])
            || isset($args['after'])
            || isset($args['before'])
        ) {
            $requested = $this->requestedCursorLimit($args, 10);

            return new Paginator($this->paginateCursorWindow($query, $args, $requested > 0 ? $this->clampPageSize($requested) : 0));
        }

        $context['filters'] = array_merge($context['filters'] ?? [], request()->query());
        $perPage = $this->pagination->getLimit($operation, $context);
        $page = $this->pagination->getPage($context);

        return new Paginator($query->paginate($perPage, ['*'], 'page', $page));
    }
}
