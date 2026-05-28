<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationMenu;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

/**
 * Returns the merged system_config tree, optionally scoped to a slug and
 * optionally embedding current values per field.
 */
class AdminConfigurationMenuProvider implements ProviderInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }

        $slug = request()->query('slug') ?: null;
        $includeValues = filter_var(request()->query('include_values'), FILTER_VALIDATE_BOOLEAN);
        $channel = request()->query('channel') ?: null;
        $locale = request()->query('locale') ?: null;

        return [self::buildPayload($this->resolver, $slug, $includeValues, $channel, $locale)];
    }

    /**
     * Shared payload builder — also used by the GraphQL resolver.
     *
     * @return array<string, mixed>
     */
    public static function buildPayload(
        AdminConfigurationSchemaResolver $resolver,
        ?string $slug,
        bool $includeValues,
        ?string $channel,
        ?string $locale,
    ): array {
        if ($slug !== null && ! $resolver->getItem($slug)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.configuration.slug-not-found'));
        }

        $tree = $slug !== null
            ? [$resolver->findSlug($slug)]
            : $resolver->toArray();

        if ($includeValues) {
            self::embedValues($tree, $resolver, $channel, $locale);
        }

        return [
            'slug' => $slug,
            'tree' => $tree,
        ];
    }

    /**
     * Walk the serialised tree and embed `value` per field via core helper.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     */
    protected static function embedValues(array &$nodes, AdminConfigurationSchemaResolver $resolver, ?string $channel, ?string $locale): void
    {
        foreach ($nodes as &$node) {
            if (! empty($node['fields'])) {
                foreach ($node['fields'] as &$field) {
                    if (($field['type'] ?? null) === 'custom') {
                        $field['value'] = null;

                        continue;
                    }

                    $field['value'] = core()->getConfigData(
                        $field['code'],
                        $channel ?: core()->getRequestedChannelCode(),
                        $locale ?: core()->getRequestedLocaleCode(),
                    );
                }
                unset($field);
            }

            if (! empty($node['children'])) {
                self::embedValues($node['children'], $resolver, $channel, $locale);
            }
        }
    }

    /**
     * Convert raw payload array → DTO instance.
     */
    public static function toDto(array $payload): AdminConfigurationMenu
    {
        $dto = new AdminConfigurationMenu;
        $dto->slug = $payload['slug'] ?? null;
        $dto->tree = $payload['tree'] ?? null;

        return $dto;
    }
}
