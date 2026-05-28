<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationValues;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

/**
 * Returns the flat effective values map for one configuration slug.
 */
class AdminConfigurationValuesProvider implements ProviderInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }

        $slug = request()->query('slug');
        $channel = request()->query('channel') ?: null;
        $locale = request()->query('locale') ?: null;

        return [self::buildPayload($this->resolver, $slug, $channel, $locale)];
    }

    /**
     * Shared payload builder — also used by the GraphQL resolver and by the
     * update processor to return the freshly-resolved values after a save.
     *
     * @return array<string, mixed>
     */
    public static function buildPayload(
        AdminConfigurationSchemaResolver $resolver,
        ?string $slug,
        ?string $channel,
        ?string $locale,
    ): array {
        if (! $slug) {
            throw new InvalidInputException(__('bagistoapi::app.admin.configuration.slug-required'), 422);
        }

        if (! $resolver->getItem($slug)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.configuration.slug-not-found'));
        }

        $effectiveChannel = $channel ?: core()->getRequestedChannelCode();
        $effectiveLocale = $locale ?: core()->getRequestedLocaleCode();

        $fields = $resolver->getFieldsUnder($slug);

        $values = [];
        foreach ($fields as $code => $field) {
            // Skip custom blade-rendered fields (no value to resolve)
            if (! empty($field['path'])) {
                continue;
            }
            $value = core()->getConfigData($code, $effectiveChannel, $effectiveLocale);
            $values[$code] = $value === null ? null : (string) (is_scalar($value) ? $value : json_encode($value));
        }

        return [
            'slug'    => $slug,
            'channel' => $effectiveChannel,
            'locale'  => $effectiveLocale,
            'values'  => $values,
        ];
    }

    public static function toDto(array $payload): AdminConfigurationValues
    {
        $dto = new AdminConfigurationValues;
        $dto->slug = $payload['slug'] ?? null;
        $dto->channel = $payload['channel'] ?? null;
        $dto->locale = $payload['locale'] ?? null;
        $dto->values = $payload['values'] ?? null;

        return $dto;
    }
}
