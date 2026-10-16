<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\Concerns\HandlesAdminMediaUpload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Helpers\MediaFileName;

/**
 * Stores an option swatch image through AttributeOptionRepository::uploadSwatchImage,
 * the same path the attribute edit form takes.
 */
class AdminAttributeOptionSwatchProcessor implements ProcessorInterface
{
    use HandlesAdminMediaUpload;

    public function __construct(
        protected AttributeOptionRepository $attributeOptionRepository,
        protected AdminAttributeItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        AdminReadPermissions::authorize(null, null, 'catalog.attributes.edit');

        $attributeId = (int) $this->pathVariable($uriVariables, 'attributeId');
        $optionId = (int) $this->pathVariable($uriVariables, 'optionId');

        $attribute = Attribute::find($attributeId);

        if (! $attribute) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.not-found'));
        }

        $option = AttributeOption::find($optionId);

        if (! $option || (int) $option->attribute_id !== $attributeId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.attribute.option-not-found'));
        }

        if ($attribute->swatch_type !== 'image') {
            throw new InvalidInputException(__('bagistoapi::app.admin.attribute.swatch-image-only'), 422);
        }

        $file = $this->validatedUpload('swatch_value', 'mimes:bmp,jpeg,jpg,png,webp', [
            'swatch_alt' => ['nullable', 'string', 'max:255'],
            'swatch_file_name' => ['nullable', 'string', 'max:'.MediaFileName::MAX_LENGTH],
        ]);

        $payload = array_merge(
            request()->only(['swatch_alt', 'swatch_file_name']),
            ['swatch_value' => $file],
        );

        Event::dispatch('catalog.attribute.update.before', $attributeId);

        $this->attributeOptionRepository->uploadSwatchImage($payload, $optionId);

        $this->attributeOptionRepository->saveSwatchAltText($payload, $optionId);

        Event::dispatch('catalog.attribute.update.after', $attribute);

        return $this->detailResponse(
            $this->itemProvider,
            Attribute::with(['translations', 'options.translations'])->find($attributeId),
        );
    }
}
