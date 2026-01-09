<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Models\DownloadableProductDownloadLink;
use Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository;

/**
 * Handles secure file downloads for purchased downloadable products
 */
class DownloadableProductController
{
    public function __construct(
        protected DownloadableLinkPurchasedRepository $downloadableLinkPurchasedRepository,
    ) {}

    public function download($token)
    {
        $downloadLink = DownloadableProductDownloadLink::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $downloadLink) {
            abort(404, __('bagistoapi::downloadable-product.download-link-not-found'));
        }

        $downloadableLinkPurchased = $this->downloadableLinkPurchasedRepository->find(
            $downloadLink->downloadable_link_purchased_id
        );

        if (! $downloadableLinkPurchased) {
            abort(404, __('bagistoapi::downloadable-product.purchased-link-not-found'));
        }

        if ($downloadableLinkPurchased->type == 'file') {
            $privateDisk = Storage::disk('private');

            if (! $privateDisk->exists($downloadableLinkPurchased->file)) {
                abort(404, __('bagistoapi::downloadable-product.file-not-found'));
            }

            $file = $privateDisk->get($downloadableLinkPurchased->file);
            $fileName = basename($downloadableLinkPurchased->file);

            return response($file, 200)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        } else {
            return redirect()->away($downloadableLinkPurchased->url);
        }
    }
}
