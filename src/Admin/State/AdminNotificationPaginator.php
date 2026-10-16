<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\Pagination\HasNextPagePaginatorInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Paginator for the notification list that adds `statusCounts` and `totalUnread` to the REST `meta`.
 */
final class AdminNotificationPaginator implements \IteratorAggregate, HasNextPagePaginatorInterface, PaginatorInterface
{
    public function __construct(
        private readonly LengthAwarePaginator $paginator,
        private readonly array $extraMeta = [],
    ) {}

    public function count(): int
    {
        return $this->paginator->count();
    }

    public function getLastPage(): float
    {
        return (float) $this->paginator->lastPage();
    }

    public function getTotalItems(): float
    {
        return (float) $this->paginator->total();
    }

    public function getCurrentPage(): float
    {
        return (float) $this->paginator->currentPage();
    }

    public function getItemsPerPage(): float
    {
        return (float) $this->paginator->perPage();
    }

    public function getIterator(): \Traversable
    {
        return $this->paginator->getIterator();
    }

    public function hasNextPage(): bool
    {
        return $this->paginator->hasMorePages();
    }

    public function getExtraMeta(): array
    {
        return $this->extraMeta;
    }
}
