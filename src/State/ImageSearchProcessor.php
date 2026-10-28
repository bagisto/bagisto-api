<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\ImageSearchInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\ImageSearch;
use Webkul\BagistoApi\State\Concerns\DecodesBase64Files;
use Webkul\Core\Traits\Sanitizer;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Product\Repositories\SearchRepository;

/**
 * Mirrors the storefront SearchController::upload.
 */
class ImageSearchProcessor implements ProcessorInterface
{
    use DecodesBase64Files;
    use Sanitizer;

    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];

    public function __construct(protected SearchRepository $searchRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ImageSearch
    {
        $image = request()->file('image');
        $image ??= $data instanceof ImageSearchInput ? $data->image : null;
        $image ??= $context['args']['input']['image'] ?? request()->input('image');

        [$imageUrl, $localPath] = $image instanceof UploadedFile ? $this->storeUpload($image) : $this->storeBase64($image);

        $keywords = '';

        $useAi = core()->getConfigData('magic_ai.general.settings.enabled')
            && core()->getConfigData('magic_ai.storefront_features.image_search.enabled');

        if ($useAi) {
            try {
                $keywords = MagicAI::analyzeImage($localPath);
            } catch (\Exception $e) {
                report($e);

                $useAi = false;
            }
        }

        $result = new ImageSearch;
        $result->image_url = $imageUrl;
        $result->keywords = $keywords;
        $result->engine = $useAi ? 'ai' : 'tensorflow';

        return $result;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeUpload(UploadedFile $image): array
    {
        $validator = Validator::make(['image' => $image], ['image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048']);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        return [$this->searchRepository->uploadSearchImage(['image' => $image]), $image->getRealPath()];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeBase64(mixed $image): array
    {
        if (! is_string($image) || $image === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.image-search.image-required'), 422);
        }

        $file = $this->decodeBase64File($image, self::MIME_TYPES, 2048 * 1024);

        $path = 'product-search/'.Str::random(40).'.'.$file['extension'];

        Storage::put($path, $file['bytes']);

        $this->sanitizeSVG($path, $file['mime']);

        return [Storage::url($path), Storage::path($path)];
    }
}
