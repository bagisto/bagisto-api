<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository;

/**
 * Generates secure download links for purchased downloadable products.
 *
 * Implements API Platform processor interface to handle BagistoApi mutations
 * for authenticated customers. Validates permissions and generates temporary
 * download tokens with automatic expiration.
 */
class DownloadableProductProcessor implements ProcessorInterface
{
    public function __construct(
        protected AuthManager $auth,
        protected DownloadableLinkPurchasedRepository $downloadableLinkPurchasedRepository,
    ) {}

    /**
     * Process BagistoApi mutation to generate a download link.
     *
     * @param  mixed  $data  Input data containing downloadableLinkPurchasedId
     * @param  Operation|null  $operation  API Platform operation metadata
     * @param  array  $uriVariables  URI variables from route
     * @param  array  $context  Request context and metadata
     * @return array Download link data with token, URL, and expiration
     *
     * @throws AuthenticationException When customer is not authenticated
     * @throws \Exception When validation fails or business rules are violated
     */
    public function process($data, ?Operation $operation = null, array $uriVariables = [], array $context = [])
    {
        $customer = $this->getAuthenticatedCustomer($data, $context);

        if (! $customer) {
            throw new AuthenticationException('Unauthorized: Customer authentication required', 401);
        }

        $downloadableLinkPurchasedId = $this->extractDownloadLinkId($data);

        if (! $downloadableLinkPurchasedId) {
            throw new \Exception('downloadableLinkPurchasedId is required', 400);
        }

        $downloadableLinkPurchased = $this->downloadableLinkPurchasedRepository->find($downloadableLinkPurchasedId);

        if (! $downloadableLinkPurchased) {
            throw new \Exception('Downloadable link not found', 404);
        }

        $this->validateCustomerOwnership($downloadableLinkPurchased, $customer);
        $this->validateDownloadStatus($downloadableLinkPurchased);
        $this->validateDownloadLimits($downloadableLinkPurchased);

        $token = Str::random(64);
        $downloadUrl = 'api/downloadable-product/download/'.$token;
        $expiresAt = now()->addHours(24);

        $downloadLink = \Webkul\BagistoApi\Models\DownloadableProductDownloadLink::create([
            'token'                          => $token,
            'url'                            => url($downloadUrl),
            'downloadable_link_purchased_id' => $downloadableLinkPurchased->id,
            'expires_at'                     => $expiresAt,
        ]);

        $this->updateDownloadUsage($downloadableLinkPurchased);

        return [
            'id'        => (string) $downloadLink->id,
            'token'     => $token,
            'url'       => url($downloadUrl),
            'expiresAt' => $expiresAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Extract downloadable link ID from input data.
     *
     * @param  mixed  $data  Input data (object or array)
     * @return int|null The downloadable link purchased ID
     */
    private function extractDownloadLinkId($data): ?int
    {
        if (is_object($data) && property_exists($data, 'downloadableLinkPurchasedId')) {
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
     * @throws \Exception When customer does not own the link
     */
    private function validateCustomerOwnership(object $downloadableLinkPurchased, Customer $customer): void
    {
        if ($downloadableLinkPurchased->order->customer_id !== $customer->id) {
            throw new \Exception('Unauthorized: This download link does not belong to you', 403);
        }
    }

    /**
     * Validate that the download is in an allowed state.
     *
     * @param  object  $downloadableLinkPurchased  The purchased link object
     *
     * @throws \Exception When download is pending
     */
    private function validateDownloadStatus(object $downloadableLinkPurchased): void
    {
        if ($downloadableLinkPurchased->status == 'pending') {
            throw new \Exception('Download is pending. Please wait for the order to be invoiced.', 403);
        }
    }

    /**
     * Validate that download limits have not been exceeded.
     *
     * @param  object  $downloadableLinkPurchased  The purchased link object
     *
     * @throws \Exception When download limits are exceeded
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
            throw new \Exception('Download limit exceeded', 403);
        }

        $remainingDownloads = $downloadableLinkPurchased->download_bought
            - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled);

        if ($downloadableLinkPurchased->download_bought && $remainingDownloads <= 0) {
            throw new \Exception('No more downloads available', 403);
        }
    }

    /**
     * Update download usage counter and status.
     *
     * @param  object  $downloadableLinkPurchased  The purchased link object
     */
    private function updateDownloadUsage(object $downloadableLinkPurchased): void
    {
        $remainingDownloads = $downloadableLinkPurchased->download_bought
            - ($downloadableLinkPurchased->download_used + $downloadableLinkPurchased->download_canceled + 1);

        $this->downloadableLinkPurchasedRepository->update([
            'download_used' => $downloadableLinkPurchased->download_used + 1,
            'status'        => $remainingDownloads <= 0 ? 'expired' : $downloadableLinkPurchased->status,
        ], $downloadableLinkPurchased->id);
    }

    /**
     * Retrieve authenticated customer from Authorization header.
     *
     * Attempts authentication via:
     * 1. Bearer token in Authorization header (via TokenHeaderFacade)
     * 2. Sanctum guard (Authorization header)
     * 3. Customer guard
     *
     * @param  mixed  $data  Input data (not used for token extraction anymore)
     * @param  array  $context  Request context
     * @return Customer|null Authenticated customer or null
     */
    private function getAuthenticatedCustomer($data, array $context): ?Customer
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract token from Authorization header via TokenHeaderFacade
        $tokenFromHeader = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if ($tokenFromHeader) {
            $customer = $this->findCustomerByToken($tokenFromHeader);
            if ($customer) {
                return $customer;
            }
        }

        $customer = auth('sanctum')->user();
        if ($customer instanceof Customer) {
            return $customer;
        }

        $customer = $this->auth->guard('customer')->user();
        if ($customer) {
            return $customer;
        }

        return $this->findCustomerFromAuthHeader($context);
    }

    /**
     * Find customer by API or Sanctum token.
     *
     * @param  string  $token  The authentication token
     * @return Customer|null The customer or null if not found
     */
    private function findCustomerByToken(string $token): ?Customer
    {
        return Customer::where('api_token', $token)
            ->orWhere('token', $token)
            ->first();
    }

    /**
     * Extract and authenticate customer from Authorization header.
     *
     * @param  array  $context  Request context
     * @return Customer|null The customer or null if not found
     */
    private function findCustomerFromAuthHeader(array $context): ?Customer
    {
        $request = $context['request'] ?? null;

        if (! $request) {
            try {
                $request = app('request');
            } catch (\Exception $e) {
                $request = null;
            }
        }

        if (! $request) {
            return null;
        }

        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        return Customer::where('api_token', $token)
            ->orWhere('token', $token)
            ->first();
    }
}
