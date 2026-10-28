<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCartItem;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCompareItem;
use Webkul\BagistoApi\Admin\Models\AdminCustomerWishlistItem;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Customer\Repositories\CompareItemRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\WishlistRepository;

/**
 * Removes a cart, wishlist or compare item from the Create Order sidebar,
 * mirroring the Admin Customers\Customer\{Cart,Wishlist,Compare}Controller::destroy actions.
 */
class AdminCustomerSidebarItemDeleteProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CartItemRepository $cartItemRepository,
        protected WishlistRepository $wishlistRepository,
        protected CompareItemRepository $compareItemRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('customers.customers.edit', 'bagistoapi::app.admin.customer.no-permission');

        [$customerId, $itemId] = $operation instanceof Mutation
            ? $this->graphQlIdentifiers($context)
            : $this->restIdentifiers($uriVariables);

        if ($customerId <= 0 || ! $this->customerRepository->find($customerId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        if ($itemId <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.sidebar.item-id-required'), 422);
        }

        $message = match ($operation->getClass()) {
            AdminCustomerCartItem::class => $this->deleteCartItem($customerId, $itemId),
            AdminCustomerWishlistItem::class => $this->deleteWishlistItem($customerId, $itemId),
            AdminCustomerCompareItem::class => $this->deleteCompareItem($customerId, $itemId),
        };

        if ($operation instanceof Mutation) {
            $class = $operation->getClass();
            $snapshot = new $class;
            $snapshot->id = $itemId;

            return $snapshot;
        }

        return new JsonResponse(['id' => $itemId, 'message' => $message], 200);
    }

    protected function deleteCartItem(int $customerId, int $itemId): string
    {
        $cartItem = $this->cartItemRepository->find($itemId);

        if (! $cartItem) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.sidebar.item-not-exists'), 422);
        }

        $cart = $cartItem->cart;

        if (! $cart || (int) $cart->customer_id !== $customerId || ! $cart->is_active) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.sidebar.item-not-found'));
        }

        Cart::setCart($cart);

        Cart::removeItem($cartItem->id);

        Cart::collectTotals();

        return __('bagistoapi::app.admin.customer.sidebar.cart-item-deleted');
    }

    protected function deleteWishlistItem(int $customerId, int $itemId): string
    {
        $item = $this->wishlistRepository->find($itemId);

        if (! $item) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.sidebar.item-not-exists'), 422);
        }

        if ((int) $item->customer_id !== $customerId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.sidebar.item-not-found'));
        }

        Event::dispatch('customer.wishlist.delete.before', $itemId);

        $this->wishlistRepository->delete($itemId);

        Event::dispatch('customer.wishlist.delete.after', $itemId);

        return __('bagistoapi::app.admin.customer.sidebar.wishlist-item-deleted');
    }

    protected function deleteCompareItem(int $customerId, int $itemId): string
    {
        $item = $this->compareItemRepository->find($itemId);

        if (! $item) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.sidebar.item-not-exists'), 422);
        }

        if ((int) $item->customer_id !== $customerId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.sidebar.item-not-found'));
        }

        $this->compareItemRepository->delete($itemId);

        return __('bagistoapi::app.admin.customer.sidebar.compare-item-deleted');
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function restIdentifiers(array $uriVariables): array
    {
        $customerId = (int) ($uriVariables['customerId'] ?? request()->route('customerId') ?? 0);

        $itemId = (int) (request()->input('item_id') ?? request()->input('itemId') ?? 0);

        return [$customerId, $itemId];
    }

    /**
     * Reads `/api/admin/customers/{customerId}/<items>/{itemId}` from the mutation `id`.
     *
     * @return array{0: int, 1: int}
     */
    protected function graphQlIdentifiers(array $context): array
    {
        $iri = (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');

        if (preg_match('#customers/(\d+)/[a-z-]+/(\d+)/?$#', $iri, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return [0, 0];
    }
}
