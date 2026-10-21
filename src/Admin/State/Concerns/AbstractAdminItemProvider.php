<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

abstract class AbstractAdminItemProvider implements ProviderInterface
{
    /**
     * The translation key passed to ResourceNotFoundException.
     * Example: 'bagistoapi::app.admin.category.not-found'.
     */
    abstract protected function getNotFoundLangKey(): string;

    /**
     * Look up the entity by primary key.
     * Return null if not found — the base class handles the 404 throw.
     */
    abstract protected function findEntity(int $id): ?object;

    /**
     * Map the Eloquent model / entity to the API resource DTO.
     */
    abstract protected function mapToDto(object $entity): object;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        AdminReadPermissions::authorize($operation, null, $this->readPermission());

        $id = (int) ($uriVariables['id'] ?? $context['args']['id'] ?? 0);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__($this->getNotFoundLangKey()));
        }

        $entity = $this->findEntity($id);

        if (! $entity) {
            throw new ResourceNotFoundException(__($this->getNotFoundLangKey()));
        }

        return $this->mapToDto($entity);
    }

    /**
     * Override to require a specific ACL key instead of the resource map entry.
     */
    protected function readPermission(): ?string
    {
        return null;
    }
}
