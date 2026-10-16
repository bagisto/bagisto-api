<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\HandlesAdminMediaUpload;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Locale;
use Webkul\Core\Repositories\LocaleRepository;

/**
 * Stores and clears a locale logo through LocaleRepository::update, which runs
 * uploadImage and fires the core.locale.update events.
 */
class AdminSettingsLocaleLogoProcessor implements ProcessorInterface
{
    use ChecksAdminPermission, HandlesAdminMediaUpload;

    public function __construct(
        protected LocaleRepository $localeRepository,
        protected AdminSettingsLocaleItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $this->authorizedAdmin('settings.locales.edit', 'bagistoapi::app.admin.settings.locale.no-permission');

        $locale = Locale::find((int) $this->pathVariable($uriVariables, 'id'));

        if (! $locale) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.locale.not-found'));
        }

        if ($operation instanceof Delete) {
            if ($locale->logo_path) {
                Storage::delete($locale->logo_path);
            }

            $this->localeRepository->update([], $locale->id);
        } else {
            $file = $this->validatedUpload('logo', ['image', 'extensions:jpeg,jpg,png,svg,webp']);

            $this->localeRepository->update(['logo_path' => [$file]], $locale->id);
        }

        return $this->detailResponse($this->itemProvider, Locale::find($locale->id));
    }
}
