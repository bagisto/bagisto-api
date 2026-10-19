<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSlug;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;

class AdminConfigurationSlugProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize($operation, AdminConfigurationSlug::class);

        return [self::buildPayload($this->resolver)];
    }

    /**
     * Build payload.
     */
    public static function buildPayload(AdminConfigurationSchemaResolver $resolver): array
    {
        return [
            'id' => 'configuration-slugs',
            'slugs' => $resolver->getSlugs(),
        ];
    }

    /**
     * To dto.
     */
    public static function toDto(array $payload): AdminConfigurationSlug
    {
        $dto = new AdminConfigurationSlug;
        $dto->slugs = $payload['slugs'] ?? null;

        return $dto;
    }
}
