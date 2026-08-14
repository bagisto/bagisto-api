<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\GraphQl\Error\ErrorHandlerInterface;
use ApiPlatform\GraphQl\ExecutorInterface;
use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\GraphQl\Serializer\SerializerContextBuilder as GraphQlSerializerContextBuilder;
use ApiPlatform\GraphQl\Type\Definition\IterableType;
use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Laravel\Eloquent\State\CollectionProvider;
use ApiPlatform\Laravel\Eloquent\State\ItemProvider;
use ApiPlatform\Laravel\Eloquent\State\LinksHandler;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\Laravel\Eloquent\State\QueryExtensionInterface;
use ApiPlatform\Laravel\GraphQl\Controller\EntrypointController;
use ApiPlatform\Laravel\GraphQl\Controller\GraphiQlController;
use ApiPlatform\Laravel\ServiceLocator;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\Pagination\Pagination;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Negotiation\Negotiator;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webkul\Attribute\Contracts\Attribute as AttributeContract;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditContext;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;
use Webkul\BagistoApi\Admin\Auth\AdminApiGuard;
use Webkul\BagistoApi\Admin\Metadata\NullableToOnePropertyMetadataFactory;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationMenuQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationSlugQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationValuesQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminDashboardQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminMenuQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminPermissionsQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminProfileQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersViewResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingOverviewQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsViewResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesViewResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Admin\State\AdminReturnMessageProcessor;
use Webkul\BagistoApi\Admin\State\AdminReturnProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusProcessor;
use Webkul\BagistoApi\CacheProfiles\ApiAwareResponseCache;
use Webkul\BagistoApi\Console\Commands\ApiKeyMaintenanceCommand;
use Webkul\BagistoApi\Console\Commands\ApiKeyManagementCommand;
use Webkul\BagistoApi\Console\Commands\ClearApiPlatformCacheCommand;
use Webkul\BagistoApi\Console\Commands\ExportSchemaCommand;
use Webkul\BagistoApi\Console\Commands\GenerateStorefrontKey;
use Webkul\BagistoApi\Console\Commands\InstallApiPlatformCommand;
use Webkul\BagistoApi\Console\Commands\OptimizeApiPlatformCommand;
use Webkul\BagistoApi\Console\Commands\PruneAuditsCommand;
use Webkul\BagistoApi\Console\Commands\PruneCartUploadsCommand;
use Webkul\BagistoApi\Console\Commands\WarmApiPlatformCacheCommand;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\GraphQl\QueryScopedSchemaBuilder;
use Webkul\BagistoApi\GraphQl\ScopedSchemaBuilder;
use Webkul\BagistoApi\GraphQl\Serializer\FixedSerializerContextBuilder;
use Webkul\BagistoApi\Http\Controllers\AdminGraphQLEntrypointController;
use Webkul\BagistoApi\Http\Controllers\AdminGraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\ApiEntrypointController;
use Webkul\BagistoApi\Http\Controllers\DownloadablePurchasedController;
use Webkul\BagistoApi\Http\Controllers\DownloadSampleController;
use Webkul\BagistoApi\Http\Controllers\GraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\InvoicePdfController;
use Webkul\BagistoApi\Http\Controllers\SwaggerUIController;
use Webkul\BagistoApi\Http\Middleware\EnforceAdminApiAuth;
use Webkul\BagistoApi\Http\Middleware\EnsureJsonContentType;
use Webkul\BagistoApi\Http\Middleware\LogApiRequests;
use Webkul\BagistoApi\Http\Middleware\RateLimitApi;
use Webkul\BagistoApi\Http\Middleware\SecurityHeaders;
use Webkul\BagistoApi\Http\Middleware\SetAdminApiAuditContext;
use Webkul\BagistoApi\Http\Middleware\SetLocaleChannel;
use Webkul\BagistoApi\Http\Middleware\ThrottleAdminApi;
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\BagistoApi\Metadata\CustomIdentifiersExtractor;
use Webkul\BagistoApi\Metadata\PathGatedResourceNameCollectionFactory;
use Webkul\BagistoApi\Metadata\SourceDocblockPropertyMetadataFactory;
use Webkul\BagistoApi\Models\CoreAttribute;
use Webkul\BagistoApi\OpenApi\SplitOpenApiFactory;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\BagistoApi\Resolver\CompareItemQueryResolver;
use Webkul\BagistoApi\Resolver\CustomerQueryResolver;
use Webkul\BagistoApi\Resolver\Factory\ProductRelationResolverFactory;
use Webkul\BagistoApi\Resolver\GdprRequestQueryResolver;
use Webkul\BagistoApi\Resolver\PageByUrlKeyResolver;
use Webkul\BagistoApi\Resolver\ProductCollectionResolver;
use Webkul\BagistoApi\Resolver\SingleProductBagistoApiResolver;
use Webkul\BagistoApi\Resolver\WishlistQueryResolver;
use Webkul\BagistoApi\Routing\CustomIriConverter;
use Webkul\BagistoApi\Serializer\AdminCollectionEnvelopeNormalizer;
use Webkul\BagistoApi\Serializer\PaginationHeaderNormalizer;
use Webkul\BagistoApi\Serializer\TokenHeaderDenormalizer;
use Webkul\BagistoApi\Services\CartTokenService;
use Webkul\BagistoApi\Services\StorefrontKeyService;
use Webkul\BagistoApi\Services\TokenHeaderService;
use Webkul\BagistoApi\State\AttributeCollectionProvider;
use Webkul\BagistoApi\State\AttributeOptionCollectionProvider;
use Webkul\BagistoApi\State\CancelOrderProcessor;
use Webkul\BagistoApi\State\CartTokenProcessor;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\BagistoApi\State\CompareItemProcessor;
use Webkul\BagistoApi\State\CompareItemProvider;
use Webkul\BagistoApi\State\CountryStateCollectionProvider;
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
use Webkul\BagistoApi\State\ProductBagistoApiProvider;
use Webkul\BagistoApi\State\ProductGraphQLProvider;
use Webkul\BagistoApi\State\ProductRelationFlagResolver;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ReorderProcessor;
use Webkul\BagistoApi\State\SnakeCaseLinksHandler;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;
use Webkul\BagistoApi\Support\CartOptionFileStaging;
use Webkul\EUWithdrawal\Services\WithdrawalService;
use Webkul\RMA\Helpers\Helper;
use Webkul\RMA\Repositories\RMAAdditionalFieldRepository;
use Webkul\RMA\Repositories\RMACustomFieldOptionRepository;
use Webkul\RMA\Repositories\RMACustomFieldRepository;
use Webkul\RMA\Repositories\RMAImageRepository;
use Webkul\RMA\Repositories\RMAItemRepository;
use Webkul\RMA\Repositories\RMAMessageRepository;
use Webkul\RMA\Repositories\RMAReasonRepository;
use Webkul\RMA\Repositories\RMAReasonResolutionRepository;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\RMA\Repositories\RMARuleRepository;
use Webkul\RMA\Repositories\RMAStatusRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\RefundRepository;

class BagistoApiServiceProvider extends ServiceProvider
{
    /**
     * Package version, surfaced as the OpenAPI `info.version`.
     */
    const BAGISTO_API_VERSION = '2.4.1';

    /**
     * Register the service provider bindings.
     */
    public function register(): void
    {
        $this->registerAdminApiGuardConfig();

        $this->mergeConfigFrom(__DIR__.'/../Admin/Config/audit.php', 'bagistoapi.audit');

        $this->mergeConfigFrom(__DIR__.'/../../config/storefront.php', 'storefront');

        $this->app->singleton(AdminApiAuditContext::class);
        $this->app->singleton(AdminApiAuditRecorder::class);

        config(['responsecache.cache_profile' => ApiAwareResponseCache::class]);

        $this->mergeAdminConfigs();

        $this->registerSnakeCaseLinksHandlerFix();

        $this->app->singleton(IterableType::class);
        $this->app->tag(IterableType::class, 'api_platform.graphql.type');

        $this->app->singleton(StorefrontKeyService::class, function ($app) {
            return new StorefrontKeyService;
        });

        $this->ensureCorsExposedHeaders(['X-Total-Count', 'X-Page', 'X-Per-Page', 'X-Total-Pages']);

        $this->app->extend(OpenApiFactoryInterface::class, function ($openApiFactory) {
            return new SplitOpenApiFactory($openApiFactory);
        });

        // Skip the ~700-route API resource enumeration for non-API HTTP requests
        // when route cache is off, so plain admin/shop web pages don't pay it.
        $this->app->extend(ResourceNameCollectionFactoryInterface::class, function ($inner) {
            return new PathGatedResourceNameCollectionFactory($inner);
        });

        $this->app->extend(
            PropertyMetadataFactoryInterface::class,
            function ($decorated) {
                return new NullableToOnePropertyMetadataFactory($decorated);
            }
        );

        $this->app->extend(
            PropertyMetadataFactoryInterface::class,
            function ($decorated) {
                return new SourceDocblockPropertyMetadataFactory($decorated);
            }
        );

        $this->app->singleton(TokenHeaderDenormalizer::class);

        $this->app->singleton('token-header-service', function ($app) {
            return new TokenHeaderService;
        });

        $this->app->alias('token-header-service', 'Webkul\BagistoApi\Services\TokenHeaderService');

        $this->app->singleton('cart-token-service', function ($app) {
            return new CartTokenService(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository'),
                $app->make('Webkul\Customer\Repositories\CustomerRepository')
            );
        });

        $this->app->alias('cart-token-service', CartTokenFacade::class);

        $this->app->singleton('Webkul\BagistoApi\Repositories\GuestCartTokensRepository', function ($app) {
            return new GuestCartTokensRepository($app);
        });

        $this->app->register(ApiStateBindingsServiceProvider::class);

        if ($this->isEuWithdrawalAvailable()) {
        }

        $this->app->singleton(AdminReturnMessageProcessor::class, function ($app) {
            return new AdminReturnMessageProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAMessageRepository::class),
            );
        });

        $this->app->singleton(AdminRmaReasonProcessor::class, function ($app) {
            return new AdminRmaReasonProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMAReasonRepository::class),
                $app->make(RMAReasonResolutionRepository::class),
            );
        });

        $this->app->singleton(AdminRmaStatusProcessor::class, function ($app) {
            return new AdminRmaStatusProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMAStatusRepository::class),
            );
        });

        $this->app->singleton(AdminRmaRuleProcessor::class, function ($app) {
            return new AdminRmaRuleProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARuleRepository::class),
            );
        });

        $this->app->singleton(AdminRmaCustomFieldProcessor::class, function ($app) {
            return new AdminRmaCustomFieldProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMACustomFieldRepository::class),
                $app->make(RMACustomFieldOptionRepository::class),
            );
        });

        if ($this->isEuWithdrawalAvailable()) {
        }

        $this->app->singleton(AdminReturnProcessor::class, function ($app) {
            return new AdminReturnProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAItemRepository::class),
                $app->make(RMAImageRepository::class),
                $app->make(RMAMessageRepository::class),
                $app->make(RMAAdditionalFieldRepository::class),
                $app->make(RMAStatusRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(OrderRepository::class),
                $app->make(RefundRepository::class),
            );
        });
        if ($this->isEuWithdrawalAvailable()) {
        }

        // Admin Order Actions (Cancel / Comment / Invoice / Shipment / Refund).
        // Sales completion — datagrid listings + Transactions/Bookings detail
        // Attributes CRUD processors + option provider
        // Attribute Families CRUD

        // Categories CRUD

        // Settings → Exchange Rates CRUD

        // Settings → Tax Rates CRUD

        // Settings → Tax Categories CRUD

        // Marketing → Catalog Rules CRUD

        // Marketing → Campaigns CRUD + send

        // Marketing → Sitemaps CRUD + generate

        // Marketing → Email Templates CRUD

        // Marketing → Events CRUD

        // Marketing → Search Synonyms CRUD

        // Marketing → URL Rewrites CRUD

        // Admin Customers CRUD + sub-resources

        // Admin Customer Groups CRUD

        // Admin Customer Reviews moderation

        // Admin Customer GDPR Requests

        // Marketing → Cart Rules CRUD

        // Settings → Locales CRUD

        // Settings → Themes (theme customizations) CRUD

        // Settings → Users (admins) CRUD

        // Catalog Products mass actions

        // Catalog Product copy

        // Catalog Product create (simple)

        // Catalog Product update (any type)

        // Catalog Product delete

        // Catalog Product images (upload / reorder / delete)

        // Admin Marketing Cart Rule Coupons (sub-resource of cart rules)

        // Admin Marketing Newsletter Subscribers

        // Admin Marketing Search Terms

        // Catalog Product inventories (list + bulk update)

        // Catalog Product customer-group prices CRUD

        // CMS Pages read-only + CRUD

        // Settings → Currencies CRUD

        // Settings → Channels CRUD

        // Settings → Data Transfer Imports (list/detail/cancel/delete)

        // Settings → Inventory Sources CRUD

        // Settings → Roles CRUD

        // Admin Cart endpoints

        // Admin Create-Order completion (draft cart, shipping/payment methods, place order)

        $this->app->tag(TokenHeaderDenormalizer::class, 'serializer.normalizer');

        $this->app->extend('api_platform_normalizer_list', function (\SplPriorityQueue $list, $app) {
            $list->insert(
                $app->make(PaginationHeaderNormalizer::class),
                1000
            );

            $list->insert(
                $app->make(AdminCollectionEnvelopeNormalizer::class),
                1100
            );

            return $list;
        });

        $this->app->singleton(CustomerProcessor::class, function ($app) {
            return new CustomerProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\BagistoApi\Validators\CustomerValidator'),
                $app->make('Webkul\Customer\Repositories\CustomerGroupRepository')
            );
        });

        $this->app->singleton(LoginProcessor::class, function ($app) {
            return new LoginProcessor(
                $app->make('Webkul\BagistoApi\Validators\LoginValidator')
            );
        });

        $this->app->singleton(CustomerProfileProcessor::class, function ($app) {
            return new CustomerProfileProcessor(
                $app->make('Webkul\BagistoApi\Validators\CustomerValidator')
            );
        });

        $this->app->singleton(CartTokenProcessor::class, function ($app) {
            return new CartTokenProcessor(
                $app->make('Webkul\Checkout\Repositories\CartRepository'),
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository'),
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

        $this->app->singleton(LogoutProcessor::class, function ($app) {
            return new LogoutProcessor;
        });

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
                $app->make(Pagination::class)
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

        $this->app->singleton(ProductBagistoApiProvider::class, function ($app) {
            return new ProductBagistoApiProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(ProductGraphQLProvider::class, function ($app) {
            return new ProductGraphQLProvider(
                $app->make(Pagination::class)
            );
        });

        // Request-scoped: membership sets loaded once per request, reused across the page.
        $this->app->singleton(ProductRelationFlagResolver::class);

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
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(DownloadableLinksProvider::class, function ($app) {
            return new DownloadableLinksProvider(
                $app->make(Pagination::class)
            );
        });

        $this->app->singleton(DownloadableSamplesProvider::class, function ($app) {
            return new DownloadableSamplesProvider(
                $app->make(Pagination::class)
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

        $this->app->singleton(ProductCollectionResolver::class);
        $this->app->tag(SingleProductBagistoApiResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CategoryCollectionResolver::class, QueryCollectionResolverInterface::class);
        $this->app->tag(BaseQueryItemResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CompareItemQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(WishlistQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(GdprRequestQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CustomerQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminProfileQueryResolver::class, QueryItemResolverInterface::class);
        // Dashboard + Block E — Reporting (read-only providers + resolvers)
        $this->app->tag(AdminDashboardQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingOverviewQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingSalesQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingCustomersQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingProductsQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingSalesViewResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingCustomersViewResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingProductsViewResolver::class, QueryItemResolverInterface::class);

        $this->app->singleton(AdminConfigurationSchemaResolver::class);
        $this->app->tag(AdminConfigurationMenuQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminConfigurationValuesQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminConfigurationSlugQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminMenuQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminPermissionsQueryResolver::class, QueryItemResolverInterface::class);

        $this->app->tag(PageByUrlKeyResolver::class, QueryCollectionResolverInterface::class);

        $this->app->extend(ResolverFactoryInterface::class, function ($resolverFactory, $app) {
            return new ProductRelationResolverFactory(
                $resolverFactory,
                $app->make(ProductRelationProvider::class)
            );
        });

        $this->app->extend(IdentifiersExtractorInterface::class, function ($extractor) {
            return new CustomIdentifiersExtractor($extractor);
        });

        $this->app->extend(IriConverterInterface::class, function ($converter, $app) {
            return new CustomIriConverter(
                $converter,
                $app->make(ResourceMetadataCollectionFactoryInterface::class)
            );
        });

        $this->app->extend(GraphQlSerializerContextBuilder::class, function ($builder, $app) {
            return new FixedSerializerContextBuilder(
                $builder,
                $app->make(NameConverterInterface::class)
            );
        });

        $this->registerScopedGraphQlEntrypoints();
    }

    protected function registerScopedGraphQlEntrypoints(): void
    {
        $scopedSchema = function ($app, bool $adminScope) {
            if (! $adminScope) {
                return new QueryScopedSchemaBuilder(
                    $app->make(ResourceNameCollectionFactoryInterface::class),
                    $app->make(ResourceMetadataCollectionFactoryInterface::class),
                    $app->make(TypesFactoryInterface::class),
                    $app->make(TypesContainerInterface::class),
                    $app->make(FieldsBuilderEnumInterface::class),
                );
            }

            return new ScopedSchemaBuilder(
                $app->make(ResourceNameCollectionFactoryInterface::class),
                $app->make(ResourceMetadataCollectionFactoryInterface::class),
                $app->make(TypesFactoryInterface::class),
                $app->make(TypesContainerInterface::class),
                $app->make(FieldsBuilderEnumInterface::class),
                $adminScope,
            );
        };

        $scopedEntrypoint = function ($app, bool $adminScope) use ($scopedSchema) {
            return new EntrypointController(
                $scopedSchema($app, $adminScope),
                $app->make(ExecutorInterface::class),
                $app->make(GraphiQlController::class),
                $app->make(SerializerInterface::class),
                $app->make(ErrorHandlerInterface::class),
                debug: (bool) config('app.debug'),
                negotiator: $app->make(Negotiator::class),
                formats: config('api-platform.formats'),
            );
        };
        if (! $this->app->environment('testing')) {
            $this->app->singleton(EntrypointController::class, function ($app) use ($scopedEntrypoint) {
                return $scopedEntrypoint($app, false);
            });
        }

        $this->app->singleton(AdminGraphQLEntrypointController::class, function ($app) use ($scopedEntrypoint) {
            return new AdminGraphQLEntrypointController(
                $scopedEntrypoint($app, true)
            );
        });
    }

    protected function registerModelSubstitutions(): void
    {
        if (! $this->app->bound('concord')) {
            return;
        }

        try {
            $this->app->make('concord')->registerModel(
                AttributeContract::class,
                CoreAttribute::class
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'bagistoapi');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webkul');

        $this->registerModelSubstitutions();

        $this->bootAdminIntegration();

        if (config('bagistoapi.audit.enabled', true)) {
            $this->app->make(AdminApiAuditRecorder::class)->register();
        }

        if ($this->isRunningAsVendorPackage()) {
            $this->publishes([
                __DIR__.'/../config/api-platform-vendor.php' => config_path('api-platform.php'),
            ], 'bagistoapi-config');
        } else {
            $this->publishes([
                __DIR__.'/../config/api-platform.php' => config_path('api-platform.php'),
            ], 'bagistoapi-config');
        }

        $this->publishes([
            __DIR__.'/../config/graphql-auth.php' => config_path('graphql-auth.php'),
            __DIR__.'/../config/storefront.php' => config_path('storefront.php'),
        ], 'bagistoapi-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/webkul'),
        ], 'bagistoapi-views');

        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('themes/admin/default/assets'),
        ], 'bagistoapi-assets');

        $this->publishes([
            __DIR__.'/../Resources/assets/css' => public_path('vendor/bagisto-api/css'),
            __DIR__.'/../Resources/assets/js' => public_path('vendor/bagisto-api/js'),
            __DIR__.'/../Resources/assets/images' => public_path('vendor/bagisto-api/images'),
        ], 'bagistoapi-graphiql-assets');

        $this->runInstallationIfNeeded();
        $this->registerApiResources();
        $this->registerApiDocumentationRoutes();
        $this->registerMiddlewareAliases();
        $this->registerGlobalMiddleware();
        $this->registerServiceProviders();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register API documentation routes.
     */
    protected function registerApiDocumentationRoutes(): void
    {
        Route::get('/api', ApiEntrypointController::class)
            ->name('bagistoapi.docs-index');

        Route::get('/api/shop', [
            SwaggerUIController::class, 'shopApi',
        ])->name('bagistoapi.shop-docs')->where('_format', '^(?!json|xml|csv)');

        Route::get('/api/admin', [
            SwaggerUIController::class, 'adminApi',
        ])->name('bagistoapi.admin-docs')->where('_format', '^(?!json|xml|csv)');

        Route::get('/api/shop/docs', [
            SwaggerUIController::class, 'shopApiDocs',
        ])->name('bagistoapi.shop-api-spec');

        Route::get('/api/admin/docs', [
            SwaggerUIController::class, 'adminApiDocs',
        ])->name('bagistoapi.admin-api-spec');

        Route::get('/api/graphiql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.graphql-playground');

        Route::get('/api/graphql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.api-graphql-playground');

        Route::get('/api/admin/graphiql', AdminGraphQLPlaygroundController::class)
            ->name('bagistoapi.admin-graphql-playground');

        Route::post(
            '/api/admin/graphql',
            AdminGraphQLEntrypointController::class
        )
            ->middleware([
                EnforceAdminApiAuth::class,
                ThrottleAdminApi::class,
                SetAdminApiAuditContext::class,
                SetLocaleChannel::class,
            ])
            ->name('bagistoapi.admin-api-graphql');

        Route::get('/api/shop/customer-invoices/{id}/pdf', InvoicePdfController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer-invoice-pdf');

        Route::get('/api/downloadable/download-sample/{type}/{id}', DownloadSampleController::class)
            ->where('type', 'link|sample')
            ->where('id', '[0-9]+')
            ->name('bagistoapi.download-sample');

        Route::get('/api/shop/customer-downloadable-products/{id}/download', DownloadablePurchasedController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer-downloadable-product-download');
    }

    /**
     * Register API resources.
     */
    protected function registerApiResources(): void
    {
        if ($this->app->bound('api_platform.metadata_factory')) {
        }
    }

    protected function isEuWithdrawalAvailable(): bool
    {
        return class_exists(WithdrawalService::class);
    }

    /**
     * Run installation if needed.
     */
    protected function runInstallationIfNeeded(): void
    {
        if (file_exists(config_path('api-platform.php'))) {
            return;
        }

        if (! $this->app->runningInConsole() || ! $this->isComposerOperation()) {
            return;
        }

        try {
            $this->app['artisan']->call('bagisto-api-platform:install', ['--quiet' => true]);
        } catch (\Exception) {
        }
    }

    /**
     * Determine if running via Composer.
     */
    protected function isComposerOperation(): bool
    {
        $composerMemory = getenv('COMPOSER_MEMORY_LIMIT');
        $composerAuth = getenv('COMPOSER_AUTH');

        return ! empty($composerMemory) || ! empty($composerAuth) || defined('COMPOSER_BINARY_PATH');
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddlewareAliases(): void
    {
        $this->app['router']->aliasMiddleware('storefront.key', VerifyStorefrontKey::class);
        $this->app['router']->aliasMiddleware('api.locale-channel', SetLocaleChannel::class);
        $this->app['router']->aliasMiddleware('api.rate-limit', RateLimitApi::class);
        $this->app['router']->aliasMiddleware('api.security-headers', SecurityHeaders::class);
        $this->app['router']->aliasMiddleware('api.log-requests', LogApiRequests::class);
    }

    protected function registerGlobalMiddleware(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(EnsureJsonContentType::class);
    }

    private function ensureCorsExposedHeaders(array $headers): void
    {
        $existing = config('cors.exposed_headers', []);
        $merged = array_values(array_unique(array_merge($existing, $headers)));

        if ($merged !== $existing) {
            config(['cors.exposed_headers' => $merged]);
        }
    }

    /**
     * Register service providers.
     */
    protected function registerServiceProviders(): void
    {
        $this->app->register(ApiPlatformExceptionHandlerServiceProvider::class);
        $this->app->register(DatabaseQueryLoggingProvider::class);
        $this->app->register(ExceptionHandlerServiceProvider::class);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            InstallApiPlatformCommand::class,
            ClearApiPlatformCacheCommand::class,
            WarmApiPlatformCacheCommand::class,
            OptimizeApiPlatformCommand::class,
            GenerateStorefrontKey::class,
            ApiKeyManagementCommand::class,
            ApiKeyMaintenanceCommand::class,
            PruneAuditsCommand::class,
            PruneCartUploadsCommand::class,
            ExportSchemaCommand::class,
        ]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('bagisto-api:prune-cart-uploads')->everyTwoHours();
        });
    }

    protected function registerSnakeCaseLinksHandlerFix(): void
    {
        $this->app->extend(
            ItemProvider::class,
            function ($original, $app) {
                $linksHandler = new SnakeCaseLinksHandler(
                    new LinksHandler(
                        $app,
                        $app->make(ResourceMetadataCollectionFactoryInterface::class)
                    )
                );

                $tagged = iterator_to_array($app->tagged(LinksHandlerInterface::class));

                return new ItemProvider(
                    $linksHandler,
                    new ServiceLocator($tagged),
                    $app->tagged(QueryExtensionInterface::class)
                );
            }
        );

        $this->app->extend(
            CollectionProvider::class,
            function ($original, $app) {
                $linksHandler = new SnakeCaseLinksHandler(
                    new LinksHandler(
                        $app,
                        $app->make(ResourceMetadataCollectionFactoryInterface::class)
                    )
                );

                $tagged = iterator_to_array($app->tagged(LinksHandlerInterface::class));

                return new CollectionProvider(
                    $app->make(Pagination::class),
                    $linksHandler,
                    $app->tagged(\ApiPlatform\Laravel\Eloquent\Extension\QueryExtensionInterface::class),
                    new ServiceLocator($tagged)
                );
            }
        );
    }

    /**
     * Check if the package is running as a vendor package.
     */
    protected function isRunningAsVendorPackage(): bool
    {
        return str_contains(__DIR__, 'vendor');
    }

    protected function registerAdminApiGuardConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Admin/Config/auth/guards.php',
            'auth.guards'
        );
    }

    /**
     * Merge the admin Integration ACL and menu configs into core arrays.
     */
    protected function mergeAdminConfigs(): void
    {
        $aclConfig = require __DIR__.'/../Admin/Config/acl.php';
        $existingAcl = (array) config('acl', []);
        config(['acl' => array_merge($existingAcl, $aclConfig)]);

        $this->mergeConfigFrom(__DIR__.'/../Admin/Config/system.php', 'core');
    }

    protected function registerIntegrationMenu(): void
    {
        if (! $this->isIntegrationModuleEnabled()) {
            return;
        }

        $menuConfig = require __DIR__.'/../Admin/Config/menu.php';
        $existingMenu = (array) config('menu.admin', []);
        config(['menu.admin' => array_merge($existingMenu, $menuConfig)]);
    }

    public function isIntegrationModuleEnabled(): bool
    {
        try {
            $value = core()->getConfigData('api.integration.settings.enabled');
        } catch (\Throwable $e) {
            return true;
        }

        return $value === null ? true : (bool) $value;
    }

    protected function bootAdminIntegration(): void
    {
        Route::middleware([
            'web',
            PreventRequestsDuringMaintenance::class,
        ])->group(__DIR__.'/../Admin/Routes/admin.php');

        $this->loadViewsFrom(__DIR__.'/../Admin/Resources/views', 'bagistoapi');

        $this->registerIntegrationMenu();

        Auth::extend('admin-api', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new AdminApiGuard(
                $provider,
                $app['request']
            );
        });

        RateLimiter::for('admin-api', function (Request $request) {
            $admin = method_exists($request, 'user') ? $request->user('admin-api') : null;

            $token = $admin?->currentAccessToken() ?? $admin?->getAttribute('current_access_token');

            if (! $token instanceof AdminPersonalAccessToken) {
                return app()->environment('testing')
                    ? Limit::none()
                    : Limit::perMinute(60)->by('admin-api-unauthenticated:'.$request->ip());
            }

            $limits = [];

            if ($token->rate_limit_per_minute !== null) {
                $limits[] = Limit::perMinute($token->rate_limit_per_minute)
                    ->by('admin-api-token:min:'.$token->id);
            }

            if ($token->rate_limit_per_day !== null) {
                $limits[] = Limit::perDay($token->rate_limit_per_day)
                    ->by('admin-api-token:day:'.$token->id);
            }

            return $limits ?: Limit::none();
        });
    }
}
