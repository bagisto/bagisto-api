<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\DownloadableProductDownloadLinkOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository;

class DownloadableProductProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected DownloadableLinkPurchasedRepository $downloadableLinkPurchasedRepository,
    ) {}

    /**
     * Check the customer may download the purchased link and return the URL that downloads it.
     *
     * @throws AuthenticationException When the customer is not authenticated.
     * @throws InvalidInputException When the link id is missing or a business rule is violated.
     * @throws AuthorizationException When the link belongs to another customer.
     * @throws ResourceNotFoundException When the purchased link does not exist.
     */
    public function process($data, ?Operation $operation = null, array $uriVariables = [], array $context = []): DownloadableProductDownloadLinkOutput
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.downloadable-product.unauthenticated'), 401);
        }

        $downloadableLinkPurchasedId = $this->extractDownloadLinkId($data);

        if (! $downloadableLinkPurchasedId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.downloadable-product.link-id-required'), 400);
        }

        $downloadableLinkPurchased = $this->downloadableLinkPurchasedRepository->find($downloadableLinkPurchasedId);

        if (! $downloadableLinkPurchased) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.downloadable-product.link-not-found'), 404);
        }

        $this->validateCustomerOwnership($downloadableLinkPurchased, $customer);
        $this->validateDownloadStatus($downloadableLinkPurchased);
        $this->validateDownloadLimits($downloadableLinkPurchased);

        $output = new DownloadableProductDownloadLinkOutput;
        $output->id = (int) $downloadableLinkPurchased->id;
        $output->url = route('bagistoapi.customer_downloadable_product_download', ['id' => $downloadableLinkPurchased->id]);

        return $output;
    }

    /**
     * Extract downloadable link ID from input data.
     *
     * @param  mixed  $data  Input data (object or array)
     * @return int|null The downloadable link purchased ID
     */
    private function extractDownloadLinkId($data): ?int
    {
        if (
            is_object($data)
            && property_exists($data, 'downloadableLinkPurchasedId')
        ) {
            return $data->downloadableLinkPurchasedId;
        }

        if (is_array($data)) {
            return $data['downloadableLinkPurchasedId']
                ?? $data['input']['downloadableLinkPurchasedId']
                ?? null;
        }

        return null;
    }

    /**
     * Validate that the download link belongs to the authenticated customer.
     *
     * @param  object  $downloadableLinkPurchased  The purchased link object
     * @param  Customer  $customer  The authenticated customer
     *
     * @throws AuthorizationException When customer does not own the link
     */
    private function validateCustomerOwnership(object $downloadableLinkPurchased, Customer $customer): void
    {
        if ($downloadableLinkPurchased->order->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.downloadable-product.not-owner'), 403);
        }
    }

    /**
     * Validate that the download is in an allowed state.
     *
     * @param  object  $downloadableLinkPurchased  The purchased link object
     *
     * @throws InvalidInputException When download is pending
     */
    private function validateDownloadStatus(object $downloadableLinkPurchased): void
    {
        if ($downloadableLinkPurchased->status == 'pending') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.downloadable-product.pending'), 403);
        }
    }

    /**
     * Validate that download limits have not been exceeded.
     *
     * @param  object  $downloadableLinkPurchased  The purchased link object
     *
     * @throws InvalidInputException When download limits are exceeded
     */
    private function validateDownloadLimits(object $downloadableLinkPurchased): void
    {
        $totalInvoiceQty = 0;

        if (isset($downloadableLinkPurchased->order->invoices)) {
            foreach ($downloadableLinkPurchased->order->invoices as $invoice) {
                $totalInvoiceQty += $invoice->total_qty;
            }
        }

        $orderedQty = $downloadableLinkPurchased->order->total_qty_ordered;
        $totalInvoiceQty *= ($downloadableLinkPurchased->download_bought / $orderedQty);

        if ($downloadableLinkPurchased->download_used >= $totalInvoiceQty) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.downloadable-product.limit-exceeded'), 403);
        }

        $remainingDownloads = $downloadableLinkPurchased->download_bought
            - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled);

        if (
            $downloadableLinkPurchased->download_bought
            && $remainingDownloads <= 0
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.downloadable-product.no-downloads-left'), 403);
        }
    }

    /**
     * Retrieve the customer behind the customer access token.
     */
    private function getAuthenticatedCustomer(): ?Customer
    {
        $customer = CurrentCustomer::user();

        return $customer instanceof Customer ? $customer : null;
    }
}
