<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Admin\Models\AdminAcl;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;

/**
 * Serialises core's acl()->getItems() tree, the one the role create and edit screens render.
 */
class AdminAclProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->build()];
    }

    public function build(): AdminAcl
    {
        AdminReadPermissions::authorize(null, null, 'settings.roles');

        $result = new AdminAcl;
        $result->tree = $this->mapItems(acl()->getItems());

        return $result;
    }

    protected function mapItems(Collection $items): array
    {
        return $items
            ->map(fn ($item) => [
                'key' => $item->key,
                'name' => $item->name,
                'route' => $item->route,
                'sort' => $item->sort,
                'children' => $this->mapItems($item->children),
            ])
            ->values()
            ->all();
    }
}
