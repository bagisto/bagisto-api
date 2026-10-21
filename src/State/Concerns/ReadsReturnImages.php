<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Http\UploadedFile;
use Webkul\BagistoApi\Exception\InvalidInputException;

trait ReadsReturnImages
{
    /**
     * Uploaded return images as the `images.N` list core reads, checked against the configured mime types.
     *
     * @return array<int, UploadedFile>
     */
    protected function returnImagesFromRequest(): array
    {
        $this->normaliseSingleReturnImage();

        if (! request()->hasFile('images')) {
            return [];
        }

        $images = array_values(array_filter((array) request()->file('images')));

        $allowed = array_filter(array_map(
            'trim',
            explode(',', (string) core()->getConfigData('sales.rma.setting.allowed_file_extension'))
        ));

        foreach ($images as $image) {
            if (
                ! empty($allowed)
                && ! in_array($image->getMimeType(), $allowed, true)
            ) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.return.invalid-image'));
            }
        }

        return $images;
    }

    /**
     * Wrap a lone `images` file into a list, since core's image upload only reads `images.N`.
     */
    protected function normaliseSingleReturnImage(): void
    {
        $files = request()->files;

        $image = $files->get('images');

        if (
            $image === null
            || is_array($image)
        ) {
            return;
        }

        $files->set('images', [$image]);
    }
}
