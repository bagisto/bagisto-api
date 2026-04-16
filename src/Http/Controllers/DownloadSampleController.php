<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Repositories\ProductDownloadableLinkRepository;
use Webkul\Product\Repositories\ProductDownloadableSampleRepository;
use Webkul\Product\Repositories\ProductRepository;

class DownloadSampleController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductDownloadableLinkRepository $productDownloadableLinkRepository,
        protected ProductDownloadableSampleRepository $productDownloadableSampleRepository,
    ) {}

    /**
     * Download sample file for a downloadable product.
     *
     * @param  string  $type  "link" or "sample"
     * @return \Illuminate\Http\Response
     */
    public function __invoke(string $type, int $id)
    {
        if ($type === 'link') {
            return $this->downloadLinkSample($id);
        }

        return $this->downloadProductSample($id);
    }

    private function downloadLinkSample(int $id)
    {
        $productDownloadableLink = $this->productDownloadableLinkRepository->find($id);

        if (! $productDownloadableLink) {
            return response()->json(['message' => 'Downloadable link not found.', 'error' => 'not_found'], 404);
        }

        if ($productDownloadableLink->sample_type === 'file') {
            $privateDisk = Storage::disk('private');

            if ($privateDisk->exists($productDownloadableLink->sample_file)) {
                return $privateDisk->download($productDownloadableLink->sample_file);
            }

            // Fallback to public disk
            if (Storage::exists($productDownloadableLink->sample_file)) {
                return Storage::download($productDownloadableLink->sample_file);
            }

            return response()->json(['message' => 'Sample file not found.', 'error' => 'file_not_found'], 404);
        }

        $fileName = basename($productDownloadableLink->sample_url);
        $tempImage = tempnam(sys_get_temp_dir(), $fileName);
        copy($productDownloadableLink->sample_url, $tempImage);

        return response()->download($tempImage, $fileName);
    }

    private function downloadProductSample(int $id)
    {
        $productDownloadableSample = $this->productDownloadableSampleRepository->find($id);

        if (! $productDownloadableSample) {
            return response()->json(['message' => 'Downloadable sample not found.', 'error' => 'not_found'], 404);
        }

        $product = $this->productRepository->find($productDownloadableSample->product_id);

        if (! $product || ! $product->visible_individually) {
            return response()->json(['message' => 'Product not found.', 'error' => 'not_found'], 404);
        }

        if ($productDownloadableSample->type === 'file') {
            // Check public disk first
            if (Storage::exists($productDownloadableSample->file)) {
                return Storage::download($productDownloadableSample->file);
            }

            // Fallback to private disk
            $privateDisk = Storage::disk('private');

            if ($privateDisk->exists($productDownloadableSample->file)) {
                return $privateDisk->download($productDownloadableSample->file);
            }

            return response()->json(['message' => 'Sample file not found.', 'error' => 'file_not_found'], 404);
        }

        $fileName = basename($productDownloadableSample->url);
        $tempImage = tempnam(sys_get_temp_dir(), $fileName);
        copy($productDownloadableSample->url, $tempImage);

        return response()->download($tempImage, $fileName);
    }
}
