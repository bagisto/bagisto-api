<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;

final class PathGatedResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $inner,
    ) {}

    /**
     * Create.
     */
    public function create(): ResourceNameCollection
    {
        return $this->shouldEnumerate()
            ? $this->inner->create()
            : new ResourceNameCollection([]);
    }

    /**
     * Should enumerate.
     */
    private function shouldEnumerate(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        if (! app()->bound('request')) {
            return true;
        }

        $path = trim(request()->getPathInfo(), '/');

        return $path === 'api' || str_starts_with($path, 'api/');
    }
}
