<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

/**
 * Centralised scaffolding for admin single-item providers.
 *
 * Handles: auth check, id resolution from uriVariables / GraphQL args,
 * 404 throw when the entity is not found.
 *
 * Concrete providers implement only:
 *   - getNotFoundLangKey() → the lang key used in ResourceNotFoundException
 *   - findEntity(int $id)  → look up the entity (with eager loads); return null if missing
 *   - mapToDto($entity)    → convert the Eloquent model to the DTO / resource class
 */
abstract class AbstractAdminItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

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
     * The translation key passed to ResourceNotFoundException.
     * Example: 'bagistoapi::app.admin.category.not-found'
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
}
