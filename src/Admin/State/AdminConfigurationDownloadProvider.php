<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Repositories\CoreConfigRepository;

/**
 * Mirrors the admin ConfigurationController::download: only a path that is the stored
 * value of a configuration row under `configuration/` can be streamed.
 */
class AdminConfigurationDownloadProvider implements ProviderInterface
{
    protected const FILE_TYPES = ['file', 'image'];

    public function __construct(
        protected AdminConfigurationSchemaResolver $resolver,
        protected CoreConfigRepository $coreConfigRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize(null, null, 'configuration');

        $code = request()->query('code');

        if (! is_string($code) || $code === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.configuration.download-code-required'), 422);
        }

        $field = $this->resolver->getField($code);

        if (! $field) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.configuration.unknown-field', ['key' => $code]));
        }

        if (! in_array($field['type'] ?? null, self::FILE_TYPES, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.configuration.download-not-file'), 422);
        }

        $value = core()->getConfigData(
            $code,
            request()->query('channel') ?: core()->getRequestedChannelCode(),
            request()->query('locale') ?: core()->getRequestedLocaleCode(),
        );

        if (
            ! is_string($value)
            || ! str_starts_with($value, 'configuration/')
            || ! $this->coreConfigRepository->findOneByField('value', $value)
            || ! Storage::exists($value)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.configuration.download-file-not-found'));
        }

        return Storage::download($value);
    }
}
