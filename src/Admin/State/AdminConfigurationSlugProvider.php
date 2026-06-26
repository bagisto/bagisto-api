<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSlug;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminConfigurationSlugProvider implements ProviderInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }

        return [self::buildPayload($this->resolver)];
    }

    public static function buildPayload(AdminConfigurationSchemaResolver $resolver): array
    {
        return [
            'id'    => 'configuration-slugs',
            'slugs' => $resolver->getSlugs(),
        ];
    }

    public static function toDto(array $payload): AdminConfigurationSlug
    {
        $dto = new AdminConfigurationSlug;
        $dto->slugs = $payload['slugs'] ?? null;

        return $dto;
    }
}
