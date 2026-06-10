<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\GraphQl\Serializer\SerializerContextBuilder as GraphQlSerializerContextBuilder;
use ApiPlatform\GraphQl\Type\Definition\IterableType;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Webkul\BagistoApi\Admin\Resolver\AdminProfileQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAttributeCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeProcessor;
use Webkul\BagistoApi\Admin\State\AdminCancelOrderProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartAddItemProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartApplyCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartPaymentMethodsProvider;
use Webkul\BagistoApi\Admin\State\AdminCartProvider;
use Webkul\BagistoApi\Admin\State\AdminCartRemoveCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartRemoveItemProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSaveAddressProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSetPaymentMethodProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSetShippingMethodProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartShippingMethodsProvider;
use Webkul\BagistoApi\Admin\State\AdminCartUpdateItemsProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDetailProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryTreeProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerCartItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerCompareItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerNoteCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerRecentOrderItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerWishlistItemProvider;
use Webkul\BagistoApi\Admin\State\AdminDraftCartProcessor;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminInvoicePrintProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceProvider;
use Webkul\BagistoApi\Admin\State\AdminOrderCommentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminOrderCommentProvider;
use Webkul\BagistoApi\Admin\State\AdminPlaceOrderProcessor;
use Webkul\BagistoApi\Admin\State\AdminProductProvider;
use Webkul\BagistoApi\Admin\State\AdminProfileProvider;
use Webkul\BagistoApi\Admin\State\AdminRefundCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundPreviewProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundProvider;
use Webkul\BagistoApi\Admin\State\AdminReorderProcessor;
use Webkul\BagistoApi\Admin\State\AdminShipmentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminShipmentProvider;
use Webkul\BagistoApi\Admin\State\OrderCollectionProvider;
use Webkul\BagistoApi\Admin\State\OrderDetailProvider;
use Webkul\BagistoApi\Console\Commands\GenerateStorefrontKey;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\GraphQl\Serializer\FixedSerializerContextBuilder;
use Webkul\BagistoApi\Http\Controllers\AdminGraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\GraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\BagistoApi\Metadata\CustomIdentifiersExtractor;
use Webkul\BagistoApi\OpenApi\SplitOpenApiFactory;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\BagistoApi\Resolver\CustomerQueryResolver;
use Webkul\BagistoApi\Resolver\Factory\ProductRelationResolverFactory;
use Webkul\BagistoApi\Resolver\PageByUrlKeyResolver;
use Webkul\BagistoApi\Resolver\ProductCollectionResolver;
use Webkul\BagistoApi\Resolver\SingleProductBagistoApiResolver;
use Webkul\BagistoApi\Routing\CustomIriConverter;
use Webkul\BagistoApi\Serializer\TokenHeaderDenormalizer;
use Webkul\BagistoApi\Services\CartTokenService;
use Webkul\BagistoApi\Services\StorefrontKeyService;
use Webkul\BagistoApi\Services\TokenHeaderService;
use Webkul\BagistoApi\State\AttributeCollectionProvider;
use Webkul\BagistoApi\State\AttributeOptionCollectionProvider;
use Webkul\BagistoApi\State\AttributeOptionQueryProvider;
use Webkul\BagistoApi\State\AttributeValueProcessor;
use Webkul\BagistoApi\State\AuthenticatedCustomerProvider;
use Webkul\BagistoApi\State\BookingSlotProvider;
use Webkul\BagistoApi\State\BundleOptionProductsProvider;
use Webkul\BagistoApi\State\CancelOrderProcessor;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;
use Webkul\BagistoApi\State\CategoryTreeProvider;
use Webkul\BagistoApi\State\ChannelProvider;
use Webkul\BagistoApi\State\CheckoutAddressProvider;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\BagistoApi\State\CompareItemItemProvider;
use Webkul\BagistoApi\State\CompareItemProcessor;
use Webkul\BagistoApi\State\CompareItemProvider;
use Webkul\BagistoApi\State\CountryStateCollectionProvider;
use Webkul\BagistoApi\State\CountryStateQueryProvider;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;
use Webkul\BagistoApi\State\CustomerDownloadableProductProvider;
use Webkul\BagistoApi\State\CustomerInvoiceProvider;
use Webkul\BagistoApi\State\CustomerOrderProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentItemProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentProvider;
use Webkul\BagistoApi\State\CustomerProcessor;
use Webkul\BagistoApi\State\CustomerProfileProcessor;
use Webkul\BagistoApi\State\CustomerReviewProvider;
use Webkul\BagistoApi\State\DefaultChannelProvider;
use Webkul\BagistoApi\State\DeleteAllCompareItemsProcessor;
use Webkul\BagistoApi\State\DeleteAllWishlistsProcessor;
use Webkul\BagistoApi\State\DownloadableLinksProvider;
use Webkul\BagistoApi\State\DownloadableProductProcessor;
use Webkul\BagistoApi\State\DownloadableSamplesProvider;
use Webkul\BagistoApi\State\FilterableAttributesProvider;
use Webkul\BagistoApi\State\ForgotPasswordProcessor;
use Webkul\BagistoApi\State\GetCheckoutAddressCollectionProvider;
use Webkul\BagistoApi\State\GroupedProductsProvider;
use Webkul\BagistoApi\State\LoginProcessor;
use Webkul\BagistoApi\State\LogoutProcessor;
use Webkul\BagistoApi\State\MoveWishlistToCartProcessor;
use Webkul\BagistoApi\State\PageProvider;
use Webkul\BagistoApi\State\PaymentMethodsProvider;
use Webkul\BagistoApi\State\Processor\ContactUsProcessor;
use Webkul\BagistoApi\State\Processor\NewsletterSubscriptionProcessor;
use Webkul\BagistoApi\State\ProductBagistoApiProvider;
use Webkul\BagistoApi\State\ProductCustomerGroupPriceProvider;
use Webkul\BagistoApi\State\ProductDetailProvider;
use Webkul\BagistoApi\State\ProductGraphQLProvider;
use Webkul\BagistoApi\State\ProductImageProvider;
use Webkul\BagistoApi\State\ProductProcessor;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\BagistoApi\State\ProductRestProvider;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ReorderProcessor;
use Webkul\BagistoApi\State\ShippingRatesProvider;
use Webkul\BagistoApi\State\VerifyTokenProcessor;
use Webkul\BagistoApi\State\WishlistItemProvider;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;

class BagistoApiServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider bindings.
     */
    public function register(): void
    {
        $this->registerAdminApiGuardConfig();

        $this->mergeConfigFrom(__DIR__.'/../Admin/Config/audit.php', 'bagistoapi.audit');

        $this->app->singleton(\Webkul\BagistoApi\Admin\Audit\AdminApiAuditContext::class);
        $this->app->singleton(\Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder::class);

        // Force the API-aware response-cache profile. Spatie's default profile caches
        // every successful GET and hashes by path only, so paginated API responses
        // (?page=2, ?itemsPerPage=5) collapse onto one cache entry.
        config(['responsecache.cache_profile' => \Webkul\BagistoApi\CacheProfiles\ApiAwareResponseCache::class]);

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

        $this->app->tag(ProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(AttributeValueProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProcessor::class, ProcessorInterface::class);
        $this->app->tag(LoginProcessor::class, ProcessorInterface::class);
        $this->app->tag(VerifyTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(LogoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ForgotPasswordProcessor::class, ProcessorInterface::class);

        // Admin API — Profile read. Clients authenticate via admin integration
        // tokens (Bearer header → AdminApiGuard).
        $this->app->tag(AdminProfileProvider::class, ProviderInterface::class);
        $this->app->tag(OrderCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(OrderDetailProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReorderProcessor::class, ProcessorInterface::class);

        // Admin Order Actions (Cancel / Comment / Invoice / Shipment / Refund).
        $this->app->tag(AdminCancelOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminOrderCommentCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminOrderCommentProvider::class, ProviderInterface::class);
        $this->app->tag(AdminInvoiceCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminInvoiceSendDuplicateProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminInvoiceMassUpdateStatusProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminInvoiceProvider::class, ProviderInterface::class);
        $this->app->tag(AdminInvoicePrintProvider::class, ProviderInterface::class);
        $this->app->tag(AdminShipmentCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminShipmentProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRefundCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRefundPreviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRefundProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminRefundExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminInvoiceExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminShipmentExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminTransactionExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminBookingExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminOrderExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateExportProvider::class, ProviderInterface::class);
        // Sales completion — datagrid listings + Transactions/Bookings detail
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminInvoiceCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminShipmentCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminRefundCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminTransactionCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminTransactionItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminBookingCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminBookingItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerAddressProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerCartItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerCompareItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerNoteCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerWishlistItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerRecentOrderItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminProductProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductDetailProvider::class, ProviderInterface::class);
        $this->app->tag(AdminAttributeCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminAttributeItemProvider::class, ProviderInterface::class);
        // Attributes CRUD processors + option provider
        $this->app->tag(AdminAttributeProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminAttributeOptionProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminAttributeOptionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminAttributeMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminAttributeFamilyCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminAttributeFamilyItemProvider::class, ProviderInterface::class);
        // Attribute Families CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminAttributeFamilyProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminAttributeFamilyWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryTreeProvider::class, ProviderInterface::class);

        // Categories CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCategoryProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCategoryWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCategoryMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCategoryMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Settings → Exchange Rates CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateUpdateRatesProcessor::class, ProcessorInterface::class);

        // Settings → Tax Rates CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateProcessor::class, ProcessorInterface::class);

        // Settings → Tax Categories CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryProcessor::class, ProcessorInterface::class);

        // Marketing → Catalog Rules CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleMassDeleteProcessor::class, ProcessorInterface::class);

        // Marketing → Campaigns CRUD + send
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCampaignCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCampaignItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCampaignWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCampaignProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCampaignSendProcessor::class, ProcessorInterface::class);

        // Marketing → Sitemaps CRUD + generate
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSitemapCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSitemapItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSitemapWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSitemapProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSitemapGenerateProcessor::class, ProcessorInterface::class);

        // Marketing → Email Templates CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingTemplateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingTemplateItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingTemplateWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingTemplateProcessor::class, ProcessorInterface::class);

        // Marketing → Events CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingEventCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingEventItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingEventWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingEventProcessor::class, ProcessorInterface::class);

        // Marketing → Search Synonyms CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymMassDeleteProcessor::class, ProcessorInterface::class);

        // Marketing → URL Rewrites CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteMassDeleteProcessor::class, ProcessorInterface::class);

        // Admin Customers CRUD + sub-resources
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerMassUpdateStatusProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerAddressItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerAddressWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerAddressProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerNoteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerImpersonateProcessor::class, ProcessorInterface::class);

        // Admin Customer Groups CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGroupCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGroupItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGroupWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGroupProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGroupMassDeleteProcessor::class, ProcessorInterface::class);

        // Admin Customer Reviews moderation
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerReviewCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerReviewItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerReviewWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerReviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerReviewMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerReviewMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Admin Customer GDPR Requests
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGdprCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGdprItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGdprWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGdprProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGdprProcessProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCustomerGdprDownloadDataProcessor::class, ProcessorInterface::class);

        // Marketing → Cart Rules CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCopyProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Locales CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsLocaleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsLocaleItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsLocaleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsLocaleProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsLocaleMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Themes (theme customizations) CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsThemeCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsThemeItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsThemeWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsThemeProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsThemeMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsThemeMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Settings → Users (admins) CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsUserCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsUserItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsUserWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsUserProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsUserDeleteSelfProcessor::class, ProcessorInterface::class);

        // Catalog Products mass actions
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Catalog Product copy
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductCopyProcessor::class, ProcessorInterface::class);

        // Catalog Product create (simple)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductCreateProcessor::class, ProcessorInterface::class);

        // Catalog Product update (any type)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductUpdateProcessor::class, ProcessorInterface::class);

        // Catalog Product delete
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductDeleteProcessor::class, ProcessorInterface::class);

        // Catalog Product images (upload / reorder / delete)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProvider::class, ProviderInterface::class);

        // Admin Marketing Cart Rule Coupons (sub-resource of cart rules)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponGenerateProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponMassDeleteProcessor::class, ProcessorInterface::class);

        // Admin Marketing Newsletter Subscribers
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberProcessor::class, ProcessorInterface::class);

        // Admin Marketing Search Terms
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermMassDeleteProcessor::class, ProcessorInterface::class);

        // Catalog Product inventories (list + bulk update)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductInventoryProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductInventoryProcessor::class, ProcessorInterface::class);

        // Catalog Product customer-group prices CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductCustomerGroupPriceProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCatalogProductCustomerGroupPriceProcessor::class, ProcessorInterface::class);

        // CMS Pages read-only + CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCmsPageCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCmsPageItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCmsPageExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCmsPageWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCmsPageProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminCmsPageMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Currencies CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Channels CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsChannelCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsChannelItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsChannelWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsChannelProcessor::class, ProcessorInterface::class);

        // Settings → Data Transfer Imports (list/detail/cancel/delete)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCancelProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportActionProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportStatsProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportDownloadProvider::class, ProviderInterface::class);

        // Settings → Inventory Sources CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Roles CRUD
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsRoleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsRoleItemProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsRoleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminSettingsRoleProcessor::class, ProcessorInterface::class);

        // Admin Cart endpoints
        $this->app->tag(AdminCartProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCartAddItemProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartUpdateItemsProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartRemoveItemProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartSaveAddressProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartApplyCouponProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartRemoveCouponProcessor::class, ProcessorInterface::class);

        // Admin Create-Order completion (draft cart, shipping/payment methods, place order)
        $this->app->tag(AdminDraftCartProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartShippingMethodsProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCartSetShippingMethodProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCartPaymentMethodsProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCartSetPaymentMethodProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminPlaceOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProfileProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerAddressTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(CartTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(CheckoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ProductReviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(CompareItemProcessor::class, ProcessorInterface::class);
        $this->app->tag(DownloadableProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(NewsletterSubscriptionProcessor::class, ProcessorInterface::class);
        $this->app->tag(WishlistProcessor::class, ProcessorInterface::class);
        $this->app->tag(MoveWishlistToCartProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllWishlistsProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllCompareItemsProcessor::class, ProcessorInterface::class);
        $this->app->tag(CancelOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ReorderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ContactUsProcessor::class, ProcessorInterface::class);

        $this->app->tag(TokenHeaderDenormalizer::class, 'serializer.normalizer');

        $this->app->extend('api_platform_normalizer_list', function (\SplPriorityQueue $list, $app) {
            $list->insert(
                $app->make(\Webkul\BagistoApi\Serializer\PaginationHeaderNormalizer::class),
                1000
            );

            // Higher priority than the header normalizer: wraps /api/admin/*
            // collection responses in the { data, meta } envelope.
            $list->insert(
                $app->make(\Webkul\BagistoApi\Serializer\AdminCollectionEnvelopeNormalizer::class),
                1100
            );

            return $list;
        });

        $this->app->singleton(CustomerProcessor::class, function ($app) {
            return new CustomerProcessor(
                $app->make('Webkul\Customer\Repositories\CustomerRepository'),
                $app->make('Webkul\BagistoApi\Validators\CustomerValidator')
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
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository')
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

        $this->app->singleton(LogoutProcessor::class, function ($app) {
            return new LogoutProcessor;
        });

        $this->app->tag(CheckoutAddressProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerAddressProvider::class, ProviderInterface::class);
        $this->app->tag(GetCheckoutAddressCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PaymentMethodsProvider::class, ProviderInterface::class);
        $this->app->tag(ShippingRatesProvider::class, ProviderInterface::class);
        $this->app->tag(AuthenticatedCustomerProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\State\CustomerProfileCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(CartTokenMutationProvider::class, ProviderInterface::class);
        $this->app->tag(ChannelProvider::class, ProviderInterface::class);
        $this->app->tag(DefaultChannelProvider::class, ProviderInterface::class);
        $this->app->tag(ProductBagistoApiProvider::class, ProviderInterface::class);
        $this->app->tag(ProductGraphQLProvider::class, ProviderInterface::class);
        $this->app->tag(ProductRestProvider::class, ProviderInterface::class);
        $this->app->tag(ProductDetailProvider::class, ProviderInterface::class);
        $this->app->tag(ProductImageProvider::class, ProviderInterface::class);
        $this->app->tag(ProductCustomerGroupPriceProvider::class, ProviderInterface::class);
        $this->app->tag(ProductRelationProvider::class, ProviderInterface::class);
        $this->app->tag(BundleOptionProductsProvider::class, ProviderInterface::class);
        $this->app->tag(GroupedProductsProvider::class, ProviderInterface::class);
        $this->app->tag(DownloadableLinksProvider::class, ProviderInterface::class);
        $this->app->tag(DownloadableSamplesProvider::class, ProviderInterface::class);
        $this->app->tag(ProductReviewProvider::class, ProviderInterface::class);
        $this->app->tag(FilterableAttributesProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeOptionCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AttributeOptionQueryProvider::class, ProviderInterface::class);
        $this->app->tag(CountryStateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(CountryStateQueryProvider::class, ProviderInterface::class);
        $this->app->tag(CategoryTreeProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\State\CategoryRestProvider::class, ProviderInterface::class);
        $this->app->tag(BookingSlotProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\State\BookingProductDetailProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\State\CursorAwareCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PageProvider::class, ProviderInterface::class);
        $this->app->tag(WishlistProvider::class, ProviderInterface::class);
        $this->app->tag(WishlistItemProvider::class, ProviderInterface::class);
        $this->app->tag(CompareItemProvider::class, ProviderInterface::class);
        $this->app->tag(CompareItemItemProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerReviewProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerDownloadableProductProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerInvoiceProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderShipmentProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerOrderShipmentItemProvider::class, ProviderInterface::class);

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
        $this->app->singleton(\Webkul\BagistoApi\State\ProductRelationFlagResolver::class);

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
                $app->make(\ApiPlatform\State\Pagination\Pagination::class)
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
        $this->app->tag(\Webkul\BagistoApi\Resolver\CompareItemQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Resolver\WishlistQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CustomerQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminProfileQueryResolver::class, QueryItemResolverInterface::class);
        // Dashboard + Block E — Reporting (read-only providers + resolvers)
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminDashboardProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingOverviewProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingSalesProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingCustomersProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingProductsProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminDashboardQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingOverviewQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingSalesViewProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingCustomersViewProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingProductsViewProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingSalesExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingCustomersExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminReportingProductsExportProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesViewResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersViewResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsViewResolver::class, QueryItemResolverInterface::class);

        // Admin Configuration (G1-G3) — shared schema resolver as singleton
        // so the system_config walk + flattened code→field map is built once
        // per request rather than once per endpoint hit.
        $this->app->singleton(\Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminConfigurationMenuProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminConfigurationValuesProvider::class, ProviderInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\State\AdminConfigurationUpdateProcessor::class, ProcessorInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminConfigurationMenuQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(\Webkul\BagistoApi\Admin\Resolver\AdminConfigurationValuesQueryResolver::class, QueryItemResolverInterface::class);

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
                $app->make(\ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface::class)
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

    /**
     * Bind the storefront and admin GraphQL entrypoints, each with a SchemaBuilder
     * scoped to its own API surface.
     *
     * The default API Platform SchemaBuilder builds query/mutation fields for ALL
     * ~261 #[ApiResource] classes on every request, and both GraphQL endpoints share
     * it — so each endpoint pays to build the OTHER surface's ~130 resources too.
     * Scoping each endpoint to its own resources roughly halves the per-request
     * schema-build cost (the single biggest GraphQL overhead on this runtime).
     */
    protected function registerScopedGraphQlEntrypoints(): void
    {
        $scopedSchema = function ($app, bool $adminScope) {
            return new \Webkul\BagistoApi\GraphQl\ScopedSchemaBuilder(
                $app->make(\ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface::class),
                $app->make(\ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface::class),
                $app->make(\ApiPlatform\GraphQl\Type\TypesFactoryInterface::class),
                $app->make(\ApiPlatform\GraphQl\Type\TypesContainerInterface::class),
                $app->make(\ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface::class),
                $adminScope,
            );
        };

        $scopedEntrypoint = function ($app, bool $adminScope) use ($scopedSchema) {
            return new \ApiPlatform\Laravel\GraphQl\Controller\EntrypointController(
                $scopedSchema($app, $adminScope),
                $app->make(\ApiPlatform\GraphQl\ExecutorInterface::class),
                $app->make(\ApiPlatform\Laravel\GraphQl\Controller\GraphiQlController::class),
                $app->make(\Symfony\Component\Serializer\SerializerInterface::class),
                $app->make(\ApiPlatform\GraphQl\Error\ErrorHandlerInterface::class),
                debug: (bool) config('app.debug'),
                negotiator: $app->make(\Negotiation\Negotiator::class),
                formats: config('api-platform.formats'),
            );
        };

        // The storefront `/api/graphql` route (registered by the API Platform Laravel
        // bridge) resolves the bridge's EntrypointController from the container. Rebind
        // it with the SHOP-scoped schema so the storefront schema excludes admin
        // resources — no route change needed.
        //
        // Exception: the admin GraphQL test suite posts admin operations to the
        // storefront `/api/graphql` URL (the test base hits one shared GraphQL URL).
        // Scoping the storefront schema in the testing environment would hide those
        // admin operations from the test endpoint. So in `testing` we leave
        // `/api/graphql` on the full schema; production storefront traffic still gets
        // the scoped (faster) schema. The dedicated `/api/admin/graphql` endpoint is
        // always admin-scoped (below), which is what production admin clients use.
        if (! $this->app->environment('testing')) {
            $this->app->singleton(\ApiPlatform\Laravel\GraphQl\Controller\EntrypointController::class, function ($app) use ($scopedEntrypoint) {
                return $scopedEntrypoint($app, false);
            });
        }

        // The admin `/api/admin/graphql` route uses our own wrapper controller, bound
        // with the ADMIN-scoped schema (excludes storefront resources).
        $this->app->singleton(\Webkul\BagistoApi\Http\Controllers\AdminGraphQLEntrypointController::class, function ($app) use ($scopedEntrypoint) {
            return new \Webkul\BagistoApi\Http\Controllers\AdminGraphQLEntrypointController(
                $scopedEntrypoint($app, true)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'bagistoapi');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webkul');

        $this->bootAdminIntegration();

        if (config('bagistoapi.audit.enabled', true)) {
            $this->app->make(\Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder::class)->register();
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
            __DIR__.'/../config/storefront.php'   => config_path('storefront.php'),
        ], 'bagistoapi-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/webkul'),
        ], 'bagistoapi-views');

        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('themes/admin/default/assets'),
        ], 'bagistoapi-assets');

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
        \Illuminate\Support\Facades\Route::get('/api', \Webkul\BagistoApi\Http\Controllers\ApiEntrypointController::class)
            ->name('bagistoapi.docs-index');

        \Illuminate\Support\Facades\Route::get('/api/shop', [
            \Webkul\BagistoApi\Http\Controllers\SwaggerUIController::class, 'shopApi',
        ])->name('bagistoapi.shop-docs')->where('_format', '^(?!json|xml|csv)');

        \Illuminate\Support\Facades\Route::get('/api/admin', [
            \Webkul\BagistoApi\Http\Controllers\SwaggerUIController::class, 'adminApi',
        ])->name('bagistoapi.admin-docs')->where('_format', '^(?!json|xml|csv)');

        \Illuminate\Support\Facades\Route::get('/api/shop/docs', [
            \Webkul\BagistoApi\Http\Controllers\SwaggerUIController::class, 'shopApiDocs',
        ])->name('bagistoapi.shop-api-spec');

        \Illuminate\Support\Facades\Route::get('/api/admin/docs', [
            \Webkul\BagistoApi\Http\Controllers\SwaggerUIController::class, 'adminApiDocs',
        ])->name('bagistoapi.admin-api-spec');

        \Illuminate\Support\Facades\Route::get('/api/graphiql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.graphql-playground');

        \Illuminate\Support\Facades\Route::get('/api/graphql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.api-graphql-playground');

        \Illuminate\Support\Facades\Route::get('/api/admin/graphiql', AdminGraphQLPlaygroundController::class)
            ->name('bagistoapi.admin-graphql-playground');

        // Dedicated admin GraphQL endpoint. Same API Platform handler/schema as
        // /api/graphql, but with admin Bearer auth (EnforceAdminApiAuth) instead
        // of the storefront key (VerifyGraphQLStorefrontKey). No back door — the
        // shop endpoint does not accept admin Bearer tokens, and this endpoint
        // does not accept storefront keys.
        \Illuminate\Support\Facades\Route::post(
            '/api/admin/graphql',
            \Webkul\BagistoApi\Http\Controllers\AdminGraphQLEntrypointController::class
        )
            ->middleware([
                \Webkul\BagistoApi\Http\Middleware\EnforceAdminApiAuth::class,
                \Webkul\BagistoApi\Http\Middleware\SetAdminApiAuditContext::class,
                \Webkul\BagistoApi\Http\Middleware\SetLocaleChannel::class,
            ])
            ->name('bagistoapi.admin-api-graphql');

        \Illuminate\Support\Facades\Route::get('/api/shop/customer-invoices/{id}/pdf', \Webkul\BagistoApi\Http\Controllers\InvoicePdfController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer-invoice-pdf');

        \Illuminate\Support\Facades\Route::get('/api/downloadable/download-sample/{type}/{id}', \Webkul\BagistoApi\Http\Controllers\DownloadSampleController::class)
            ->where('type', 'link|sample')
            ->where('id', '[0-9]+')
            ->name('bagistoapi.download-sample');

        \Illuminate\Support\Facades\Route::get('/api/shop/customer-downloadable-products/{id}/download', \Webkul\BagistoApi\Http\Controllers\DownloadablePurchasedController::class)
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
            // Installation can be run manually if needed
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
        $this->app['router']->aliasMiddleware('api.locale-channel', \Webkul\BagistoApi\Http\Middleware\SetLocaleChannel::class);
        $this->app['router']->aliasMiddleware('api.rate-limit', \Webkul\BagistoApi\Http\Middleware\RateLimitApi::class);
        $this->app['router']->aliasMiddleware('api.security-headers', \Webkul\BagistoApi\Http\Middleware\SecurityHeaders::class);
        $this->app['router']->aliasMiddleware('api.log-requests', \Webkul\BagistoApi\Http\Middleware\LogApiRequests::class);
    }

    /**
     * Register global middleware that runs on every HTTP request.
     * EnsureJsonContentType lets bodyless POST endpoints (e.g., delete-all-*)
     * work without clients needing to send a Content-Type header.
     */
    protected function registerGlobalMiddleware(): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->prependMiddleware(\Webkul\BagistoApi\Http\Middleware\EnsureJsonContentType::class);
    }

    /**
     * Make our X-* pagination headers visible to JS clients via CORS without
     * requiring users to edit config/cors.php.
     */
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
            \Webkul\BagistoApi\Console\Commands\InstallApiPlatformCommand::class,
            \Webkul\BagistoApi\Console\Commands\ClearApiPlatformCacheCommand::class,
            \Webkul\BagistoApi\Console\Commands\WarmApiPlatformCacheCommand::class,
            \Webkul\BagistoApi\Console\Commands\OptimizeApiPlatformCommand::class,
            GenerateStorefrontKey::class,
            \Webkul\BagistoApi\Console\Commands\ApiKeyManagementCommand::class,
            \Webkul\BagistoApi\Console\Commands\ApiKeyMaintenanceCommand::class,
            \Webkul\BagistoApi\Console\Commands\PruneAuditsCommand::class,
        ]);
    }

    /**
     * Override API Platform's ItemProvider and CollectionProvider to wrap the
     * LinksHandler with SnakeCaseLinksHandler, fixing the camelCase/snake_case
     * mismatch between GraphQL field names and Eloquent relationship names.
     */
    protected function registerSnakeCaseLinksHandlerFix(): void
    {
        $this->app->extend(
            \ApiPlatform\Laravel\Eloquent\State\ItemProvider::class,
            function ($original, $app) {
                $linksHandler = new \Webkul\BagistoApi\State\SnakeCaseLinksHandler(
                    new \ApiPlatform\Laravel\Eloquent\State\LinksHandler(
                        $app,
                        $app->make(\ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface::class)
                    )
                );

                $tagged = iterator_to_array($app->tagged(\ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface::class));

                return new \ApiPlatform\Laravel\Eloquent\State\ItemProvider(
                    $linksHandler,
                    new \ApiPlatform\Laravel\ServiceLocator($tagged),
                    $app->tagged(\ApiPlatform\Laravel\Eloquent\State\QueryExtensionInterface::class)
                );
            }
        );

        $this->app->extend(
            \ApiPlatform\Laravel\Eloquent\State\CollectionProvider::class,
            function ($original, $app) {
                $linksHandler = new \Webkul\BagistoApi\State\SnakeCaseLinksHandler(
                    new \ApiPlatform\Laravel\Eloquent\State\LinksHandler(
                        $app,
                        $app->make(\ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface::class)
                    )
                );

                $tagged = iterator_to_array($app->tagged(\ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface::class));

                return new \ApiPlatform\Laravel\Eloquent\State\CollectionProvider(
                    $app->make(\ApiPlatform\State\Pagination\Pagination::class),
                    $linksHandler,
                    $app->tagged(\ApiPlatform\Laravel\Eloquent\Extension\QueryExtensionInterface::class),
                    new \ApiPlatform\Laravel\ServiceLocator($tagged)
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

    /**
     * Merge the admin-api guard config into Laravel's auth.guards array.
     * Follows the Bagisto package pattern: separate config file merged into
     * `auth.guards` without touching the application's config/auth.php.
     */
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

        // System configuration — adds the Admin → Configuration → API entries,
        // including the Integration module enable/disable toggle.
        $this->mergeConfigFrom(__DIR__.'/../Admin/Config/system.php', 'core');
    }

    /**
     * Register the Integration sidebar menu — only when the module is enabled.
     *
     * Runs in boot() (not register()) because the enabled flag is read from the
     * core_config DB table via core()->getConfigData(), which is not reliably
     * available during the register phase.
     */
    protected function registerIntegrationMenu(): void
    {
        if (! $this->isIntegrationModuleEnabled()) {
            return;
        }

        $menuConfig = require __DIR__.'/../Admin/Config/menu.php';
        $existingMenu = (array) config('menu.admin', []);
        config(['menu.admin' => array_merge($existingMenu, $menuConfig)]);
    }

    /**
     * Whether the API Integration module is enabled in system configuration.
     *
     * Defaults to enabled — including when the config table is unavailable
     * (e.g. during `config:cache`, migrations, or before installation).
     */
    public function isIntegrationModuleEnabled(): bool
    {
        try {
            $value = core()->getConfigData('api.integration.settings.enabled');
        } catch (\Throwable $e) {
            return true;
        }

        return $value === null ? true : (bool) $value;
    }

    /**
     * Bootstrap the admin integration module: guard driver, routes, views,
     * and the rate limiter used by /api/admin/* protected by auth:admin-api.
     */
    protected function bootAdminIntegration(): void
    {
        \Illuminate\Support\Facades\Route::middleware([
            'web',
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        ])->group(__DIR__.'/../Admin/Routes/admin.php');

        $this->loadViewsFrom(__DIR__.'/../Admin/Resources/views', 'bagistoapi');

        $this->registerIntegrationMenu();

        \Illuminate\Support\Facades\Auth::extend('admin-api', function ($app, $name, array $config) {
            $provider = \Illuminate\Support\Facades\Auth::createUserProvider($config['provider']);

            return new \Webkul\BagistoApi\Admin\Auth\AdminApiGuard(
                $provider,
                $app['request']
            );
        });

        \Illuminate\Support\Facades\RateLimiter::for('admin-api', function (\Illuminate\Http\Request $request) {
            $token = method_exists($request, 'user') ? $request->user('admin-api')?->getAttribute('current_access_token') : null;

            if (! $token instanceof \Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken) {
                return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
            }

            $limits = [];

            if ($token->rate_limit_per_minute !== null) {
                $limits[] = \Illuminate\Cache\RateLimiting\Limit::perMinute($token->rate_limit_per_minute)
                    ->by('admin-api-token:'.$token->id);
            }

            if ($token->rate_limit_per_day !== null) {
                $limits[] = \Illuminate\Cache\RateLimiting\Limit::perDay($token->rate_limit_per_day)
                    ->by('admin-api-token:'.$token->id);
            }

            return $limits ?: \Illuminate\Cache\RateLimiting\Limit::none();
        });
    }
}
