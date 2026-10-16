<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\ImageSearchInput;
use Webkul\BagistoApi\Dto\ImageSearchOutput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Product\Repositories\SearchRepository;

class ImageSearchProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(protected SearchRepository $searchRepository) {}

    /**
     * Store a search image, after validating its content as core's image search does, and return its
     * URL with the keywords Magic AI read from it when that feature is on.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ImageSearchOutput
    {
        $key = 'image-search:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.rate-limit.too-many-attempts', ['seconds' => RateLimiter::availableIn($key)]), 429);
        }

        RateLimiter::hit($key, 60);

        $upload = request()->file('image');
        $image = $upload instanceof UploadedFile ? $upload : $this->fromInput($data);

        try {
            $this->validateImage($image);

            $imageUrl = $this->storeImage($image);
            $useAi = (bool) core()->getConfigData('magic_ai.general.settings.enabled')
                && (bool) core()->getConfigData('magic_ai.storefront_features.image_search.enabled');
            $keywords = '';

            if ($useAi) {
                try {
                    $keywords = (string) MagicAI::analyzeImage($image->getRealPath());
                } catch (\Throwable $exception) {
                    report($exception);
                    $useAi = false;
                }
            }
        } finally {
            if (
                $image
                && $image !== $upload
            ) {
                $this->deleteTemporaryFile($image);
            }
        }

        $output = new ImageSearchOutput;
        $output->imageUrl = $imageUrl;
        $output->keywords = $keywords;
        $output->engine = $useAi ? 'ai' : 'tensorflow';

        return $output;
    }

    /**
     * Build the image from the base64 GraphQL input, when one was sent.
     */
    private function fromInput(mixed $data): ?UploadedFile
    {
        if (
            ! $data instanceof ImageSearchInput
            || $data->image === ''
        ) {
            return null;
        }

        return $this->fromBase64($data->image, $data->file_name, $data->mime_type);
    }

    /**
     * Apply core's image search rules to the file itself, so its real content type decides, never the
     * name or type the client sent.
     */
    private function validateImage(?UploadedFile $image): void
    {
        $validator = Validator::make(['image' => $image], [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-image'), 422);
        }
    }

    /**
     * Store a search image and return its public URL.
     */
    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('product-search');

        $this->searchRepository->sanitizeSVG($path, $image->getMimeType());

        return Storage::url($path);
    }

    /**
     * Build an uploaded file from a base64 string, accepting an optional data URI prefix.
     */
    private function fromBase64(string $encoded, ?string $fileName, ?string $mimeType): UploadedFile
    {
        $payload = $encoded;

        if (preg_match('#^data:(?<mime>[^;]+);base64,(?<data>.*)$#s', $encoded, $matches) === 1) {
            $mimeType = $mimeType ?: $matches['mime'];
            $payload = $matches['data'];
        }

        $binary = base64_decode($payload, true);

        if ($binary === false) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-image'), 422);
        }

        $name = $fileName ?: 'image';

        if (pathinfo($name, PATHINFO_EXTENSION) === '') {
            $name .= '.'.$this->extensionFor($mimeType);
        }

        $path = tempnam(sys_get_temp_dir(), 'image-search-');
        file_put_contents($path, $binary);

        return new UploadedFile($path, $name, $mimeType, null, true);
    }

    /**
     * Remove the temporary file a base64 image was decoded into.
     */
    private function deleteTemporaryFile(UploadedFile $image): void
    {
        $path = $image->getPathname();

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Map an image mime type to a file extension.
     */
    private function extensionFor(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
            default => 'png',
        };
    }
}
