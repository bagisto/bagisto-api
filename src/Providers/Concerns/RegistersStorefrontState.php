<?php

namespace Webkul\BagistoApi\Providers\Concerns;

use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\State\Pagination\Pagination;
use Webkul\BagistoApi\Services\Cart\GuestCartMerger;
use Webkul\BagistoApi\State\AttributeCollectionProvider;
use Webkul\BagistoApi\State\AttributeOptionCollectionProvider;
use Webkul\BagistoApi\State\CancelOrderProcessor;
use Webkul\BagistoApi\State\CartTokenProcessor;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\BagistoApi\State\CompareItemProcessor;
use Webkul\BagistoApi\State\CompareItemProvider;
use Webkul\BagistoApi\State\CountryStateCollectionProvider;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\BagistoApi\State\CustomerDownloadableProductProvider;
use Webkul\BagistoApi\State\CustomerInvoiceProvider;
use Webkul\BagistoApi\State\CustomerOrderProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentItemProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentProvider;
use Webkul\BagistoApi\State\CustomerProcessor;
use Webkul\BagistoApi\State\CustomerProfileProcessor;
use Webkul\BagistoApi\State\CustomerReturnMessageProcessor;
use Webkul\BagistoApi\State\CustomerReturnProcessor;
use Webkul\BagistoApi\State\CustomerReviewProvider;
use Webkul\BagistoApi\State\DeleteAllCompareItemsProcessor;
use Webkul\BagistoApi\State\DeleteAllWishlistsProcessor;
use Webkul\BagistoApi\State\DownloadableLinksProvider;
use Webkul\BagistoApi\State\DownloadableSamplesProvider;
use Webkul\BagistoApi\State\EuWithdrawalProcessor;
use Webkul\BagistoApi\State\FilterableAttributesProvider;
use Webkul\BagistoApi\State\GdprRequestProvider;
use Webkul\BagistoApi\State\GetCheckoutAddressCollectionProvider;
use Webkul\BagistoApi\State\GroupedProductsProvider;
use Webkul\BagistoApi\State\LoginProcessor;
use Webkul\BagistoApi\State\LogoutProcessor;
use Webkul\BagistoApi\State\MoveWishlistToCartProcessor;
use Webkul\BagistoApi\State\ProductGraphQLProvider;
use Webkul\BagistoApi\State\ProductRelationFlagResolver;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ReorderProcessor;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;
use Webkul\BagistoApi\Support\CartOptionFileStaging;
use Webkul\EUWithdrawal\Services\WithdrawalService;
use Webkul\RMA\Helpers\Helper;
use Webkul\RMA\Repositories\RMAAdditionalFieldRepository;
use Webkul\RMA\Repositories\RMACustomFieldRepository;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAItemRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\Sales\Repositories\OrderRepository;

trait RegistersStorefrontState
{
    /**
     * Register the storefront processors.
     */
    protected function registerStorefrontProcessors(): void
    {
        $this->app->singleton(CustomerProcessor::class);

        $this->app->singleton(LoginProcessor::class);

        $this->app->singleton(CustomerProfileProcessor::class, function ($app) {
            return new CustomerProfileProcessor(
                $app->make('Webkul\BagistoApi\Validators\CustomerValidator')
            );
        });

        $this->app->singleton(CartTokenProcessor::class, function ($app) {
            return new CartTokenProcessor(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository'),
                $app->make(GuestCartMerger::class),
                $app->make(CartOptionFileStaging::class)
            );
        });

        $this->app->singleton(CheckoutProcessor::class, function ($app) {
            return new CheckoutProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\Sales\Repositories\OrderRepository'),
                $app->make('Webkul\Checkout\Repositories\CartRepository')
            );
        });

        $this->app->singleton(ProductReviewProcessor::class, function ($app) {
            return new ProductReviewProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CompareItemProcessor::class, function ($app) {
            return new CompareItemProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(WishlistProcessor::class, function ($app) {
            return new WishlistProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(MoveWishlistToCartProcessor::class, function ($app) {
            return new MoveWishlistToCartProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(DeleteAllWishlistsProcessor::class, function ($app) {
            return new DeleteAllWishlistsProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(DeleteAllCompareItemsProcessor::class, function ($app) {
            return new DeleteAllCompareItemsProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CancelOrderProcessor::class, function ($app) {
            return new CancelOrderProcessor(
                $app->make(PersistProcessor::class),
                $app->make('Webkul\Sales\Repositories\OrderRepository')
            );
        });

        $this->app->singleton(ReorderProcessor::class, function ($app) {
            return new ReorderProcessor(
                $app->make(PersistProcessor::class)
            );
        });

        $this->app->singleton(CustomerReturnProcessor::class, function ($app) {
            return new CustomerReturnProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAItemRepository::class),
                $app->make(RMAImageRepository::class),
                $app->make(RMAMessageRepository::class),
                $app->make(Helper::class),
                $app->make(OrderRepository::class),
                $app->make(RMACustomFieldRepository::class),
                $app->make(RMAAdditionalFieldRepository::class),
            );
        });

        if ($this->isEuWithdrawalAvailable()) {
            $this->app->singleton(EuWithdrawalProcessor::class, function ($app) {
                return new EuWithdrawalProcessor(
                    $app->make(PersistProcessor::class),
                    $app->make(OrderRepository::class),
                    $app->make(WithdrawalService::class),
                );
            });
        }

        $this->app->singleton(CustomerReturnMessageProcessor::class, function ($app) {
            return new CustomerReturnMessageProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAMessageRepository::class),
            );
        });

        $this->app->singleton(LogoutProcessor::class);
    }

    /**
     * Register the storefront providers.
     */
    protected function registerStorefrontProviders(): void
    {
        $this->app->singleton(GetCheckoutAddressCollectionProvider::class, function ($app) {
            return new GetCheckoutAddressCollectionProvider(
                $app->make('ApiPlatform\State\Pagination\Pagination')
            );
        });

        $this->app->singleton(WishlistProvider::class, function ($app) {
            return new WishlistProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(GdprRequestProvider::class, function ($app) {
            return new GdprRequestProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CompareItemProvider::class, function ($app) {
            return new CompareItemProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerReviewProvider::class, function ($app) {
            return new CustomerReviewProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderProvider::class, function ($app) {
            return new CustomerOrderProvider(
                $app->make(Pagination::class),
                $app->make(OrderRepository::class)
            );
        });

        $this->app->singleton(CustomerDownloadableProductProvider::class, function ($app) {
            return new CustomerDownloadableProductProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerInvoiceProvider::class, function ($app) {
            return new CustomerInvoiceProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderShipmentProvider::class, function ($app) {
            return new CustomerOrderShipmentProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerOrderShipmentItemProvider::class, function ($app) {
            return new CustomerOrderShipmentItemProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CustomerAddressProvider::class, function ($app) {
            return new CustomerAddressProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductGraphQLProvider::class, function ($app) {
            return new ProductGraphQLProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->scoped(ProductRelationFlagResolver::class);

        $this->app->singleton(ProductRelationProvider::class, function ($app) {
            return new ProductRelationProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductReviewProvider::class, function ($app) {
            return new ProductReviewProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(GroupedProductsProvider::class, function ($app) {
            return new GroupedProductsProvider(
                $app->make(Pagination::class),
                $app->make(CursorAwareCollectionProvider::class),
            );
        });

        $this->app->singleton(DownloadableLinksProvider::class, function ($app) {
            return new DownloadableLinksProvider(
                $app->make(Pagination::class),
                $app->make(CursorAwareCollectionProvider::class),
            );
        });

        $this->app->singleton(DownloadableSamplesProvider::class, function ($app) {
            return new DownloadableSamplesProvider(
                $app->make(Pagination::class),
                $app->make(CursorAwareCollectionProvider::class),
            );
        });

        $this->app->singleton(FilterableAttributesProvider::class, function ($app) {
            return new FilterableAttributesProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(AttributeCollectionProvider::class, function ($app) {
            return new AttributeCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(AttributeOptionCollectionProvider::class, function ($app) {
            return new AttributeOptionCollectionProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(CountryStateCollectionProvider::class, function ($app) {
            return new CountryStateCollectionProvider(
                $app->make(Pagination::class)
            );
        });
    }
}
