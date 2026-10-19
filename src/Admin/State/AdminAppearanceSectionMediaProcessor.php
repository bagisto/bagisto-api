<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Repositories\SectionRepository;

class AdminAppearanceSectionMediaProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('appearance.sections.edit', 'bagistoapi::app.admin.appearance.no-permission');

        $id = $this->scope->pathValue($uriVariables, 'id');

        $section = $this->scope->sectionOrFail($id ? (int) $id : null);

        $file = request()->file('file');

        if (! $file) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.file-required'), 422);
        }

        $validator = Validator::make(['file' => $file], [
            'file' => 'required|mimes:bmp,jpeg,jpg,png,webp,mp4,webm,ogg|max:51200',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        Event::dispatch('section.media.upload.before', $section->id);

        $media = $this->sectionRepository->storeMedia($section->id, $file);

        Event::dispatch('section.media.upload.after', $media);

        return new JsonResponse([
            'sectionId' => (int) $section->id,
            'path' => $media['path'] ?? null,
            'type' => $media['type'] ?? null,
            'message' => __('bagistoapi::app.admin.appearance.section.media-uploaded'),
        ], 201);
    }
}
