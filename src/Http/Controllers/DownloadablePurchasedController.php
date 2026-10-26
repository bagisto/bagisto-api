<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository;
use Webkul\Shop\Traits\ValidatesExternalUrl;

class DownloadablePurchasedController extends Controller
{
    use ValidatesExternalUrl;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected DownloadableLinkPurchasedRepository $downloadableLinkPurchasedRepository,
    ) {}

    /**
     * Download a purchased downloadable product file, counting the download only once the file is there.
     */
    public function __invoke(int $id): Response
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            return $this->refuse('unauthenticated', 'unauthenticated', 401);
        }

        $downloadableLinkPurchased = $this->downloadableLinkPurchasedRepository->findOneByField([
            'id' => $id,
            'customer_id' => $customer->id,
        ]);

        if (! $downloadableLinkPurchased) {
            return $this->refuse('not-found', 'not_found', 404);
        }

        if ($downloadableLinkPurchased->status === 'pending') {
            return $this->refuse('pending', 'download_pending', 403);
        }

        $totalInvoiceQty = 0;

        if (isset($downloadableLinkPurchased->order->invoices)) {
            foreach ($downloadableLinkPurchased->order->invoices as $invoice) {
                $totalInvoiceQty += $invoice->total_qty;
            }
        }

        $orderedQty = $downloadableLinkPurchased->order->total_qty_ordered;
        $totalInvoiceQty = $totalInvoiceQty * ($downloadableLinkPurchased->download_bought / $orderedQty);

        if ($downloadableLinkPurchased->download_used >= $totalInvoiceQty) {
            return $this->refuse('limit-exceeded', 'download_limit_exceeded', 403);
        }

        if (
            $downloadableLinkPurchased->download_bought
            && ($downloadableLinkPurchased->download_bought - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled)) <= 0
        ) {
            return $this->refuse('no-downloads-remaining', 'no_downloads_remaining', 403);
        }

        if ($downloadableLinkPurchased->type === 'file') {
            if (! Storage::disk('private')->exists($downloadableLinkPurchased->file)) {
                return $this->refuse('file-not-found', 'file_not_found', 404);
            }

            $this->countDownload($downloadableLinkPurchased);

            return Storage::disk('private')->download($downloadableLinkPurchased->file);
        }

        if (! $this->validateExternalUrl($downloadableLinkPurchased->url)) {
            return $this->refuse('link-not-found', 'not_found', 404);
        }

        $fileName = basename($downloadableLinkPurchased->url);
        $tempImage = tempnam(sys_get_temp_dir(), $fileName);
        copy($downloadableLinkPurchased->url, $tempImage);

        $this->countDownload($downloadableLinkPurchased);

        return response()->download($tempImage, $fileName);
    }

    /**
     * Record one download, expiring the link once none remain, as core's shop download does.
     */
    protected function countDownload(object $downloadableLinkPurchased): void
    {
        if (! $downloadableLinkPurchased->download_bought) {
            return;
        }

        $remainingDownloads = $downloadableLinkPurchased->download_bought
            - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled + 1);

        $this->downloadableLinkPurchasedRepository->update([
            'download_used' => $downloadableLinkPurchased->download_used + 1,
            'status' => $remainingDownloads <= 0 ? 'expired' : $downloadableLinkPurchased->status,
        ], $downloadableLinkPurchased->id);
    }

    /**
     * The JSON refusal for a download that cannot be served.
     */
    protected function refuse(string $messageKey, string $error, int $status): JsonResponse
    {
        return response()->json([
            'message' => __('bagistoapi::app.graphql.customer-downloadable-product.download.'.$messageKey),
            'error' => $error,
        ], $status);
    }
}
