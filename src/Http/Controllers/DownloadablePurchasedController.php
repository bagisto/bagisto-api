<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository;

class DownloadablePurchasedController extends Controller
{
    public function __construct(
        protected DownloadableLinkPurchasedRepository $downloadableLinkPurchasedRepository,
    ) {}

    /**
     * Download a purchased downloadable product file.
     *
     * @param  int  $id  Downloadable link purchased ID (_id from customerDownloadableProducts)
     * @return Response
     */
    public function __invoke(int $id)
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            return response()->json([
                'message' => 'Unauthorized: Customer authentication required.',
                'error' => 'unauthenticated',
            ], 401);
        }

        $downloadableLinkPurchased = $this->downloadableLinkPurchasedRepository->findOneByField([
            'id' => $id,
            'customer_id' => $customer->id,
        ]);

        if (! $downloadableLinkPurchased) {
            return response()->json([
                'message' => 'Downloadable product not found.',
                'error' => 'not_found',
            ], 404);
        }

        if ($downloadableLinkPurchased->status === 'pending') {
            return response()->json([
                'message' => 'Download is pending. Please wait for the order to be invoiced.',
                'error' => 'download_pending',
            ], 403);
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
            return response()->json([
                'message' => 'Download limit exceeded.',
                'error' => 'download_limit_exceeded',
            ], 403);
        }

        if (
            $downloadableLinkPurchased->download_bought
            && ($downloadableLinkPurchased->download_bought - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled)) <= 0
        ) {
            return response()->json([
                'message' => 'No more downloads available.',
                'error' => 'no_downloads_remaining',
            ], 403);
        }

        $remainingDownloads = $downloadableLinkPurchased->download_bought
            - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled + 1);

        if ($downloadableLinkPurchased->download_bought) {
            $this->downloadableLinkPurchasedRepository->update([
                'download_used' => $downloadableLinkPurchased->download_used + 1,
                'status' => $remainingDownloads <= 0 ? 'expired' : $downloadableLinkPurchased->status,
            ], $downloadableLinkPurchased->id);
        }

        if ($downloadableLinkPurchased->type === 'file') {
            $privateDisk = Storage::disk('private');

            if (! $privateDisk->exists($downloadableLinkPurchased->file)) {
                return response()->json([
                    'message' => 'File not found.',
                    'error' => 'file_not_found',
                ], 404);
            }

            return $privateDisk->download($downloadableLinkPurchased->file);
        }

        $fileName = basename($downloadableLinkPurchased->url);
        $tempImage = tempnam(sys_get_temp_dir(), $fileName);
        copy($downloadableLinkPurchased->url, $tempImage);

        return response()->download($tempImage, $fileName);
    }
}
