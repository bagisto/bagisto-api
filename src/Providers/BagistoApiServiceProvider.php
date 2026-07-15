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
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
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
use Webkul\BagistoApi\Admin\State\AdminAttributeCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeProcessor;
use Webkul\BagistoApi\Admin\State\AdminBookingCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminBookingExportProvider;
use Webkul\BagistoApi\Admin\State\AdminBookingItemProvider;
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
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCopyProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCustomerGroupPriceProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCustomerGroupPriceProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDetailProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDownloadableFileProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDownloadableFileProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductExportProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductImageProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductInventoryProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductInventoryProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductUpdateProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductVideoProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductVideoProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCategoryMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminCategoryProcessor;
use Webkul\BagistoApi\Admin\State\AdminCategoryTreeProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageExportProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCmsPageProcessor;
use Webkul\BagistoApi\Admin\State\AdminCmsPageWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminConfigurationMenuProvider;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSlugProvider;
use Webkul\BagistoApi\Admin\State\AdminConfigurationUpdateProcessor;
use Webkul\BagistoApi\Admin\State\AdminConfigurationValuesProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerAddressWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerCartItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerCompareItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprDownloadDataProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprProcessProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGdprWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerImpersonateProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerNoteCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerNoteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerRecentOrderItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerReviewWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerWishlistItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminDashboardProvider;
use Webkul\BagistoApi\Admin\State\AdminDraftCartProcessor;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalItemProvider;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalProcessor;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminInvoiceExportProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminInvoicePrintProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceSendDuplicateProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignSendProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCampaignWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCopyProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponGenerateProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapGenerateProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingTemplateWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminMenuProvider;
use Webkul\BagistoApi\Admin\State\AdminOrderCommentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminOrderCommentProvider;
use Webkul\BagistoApi\Admin\State\AdminOrderExportProvider;
use Webkul\BagistoApi\Admin\State\AdminPermissionsProvider;
use Webkul\BagistoApi\Admin\State\AdminPlaceOrderProcessor;
use Webkul\BagistoApi\Admin\State\AdminProductProvider;
use Webkul\BagistoApi\Admin\State\AdminProfileProvider;
use Webkul\BagistoApi\Admin\State\AdminRefundCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRefundCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundExportProvider;
use Webkul\BagistoApi\Admin\State\AdminRefundPreviewProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundProvider;
use Webkul\BagistoApi\Admin\State\AdminReorderProcessor;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersExportProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersViewProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingOverviewProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsExportProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsViewProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesExportProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesViewProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnableItemProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnItemProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnMessageProcessor;
use Webkul\BagistoApi\Admin\State\AdminReturnMessageProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnProcessor;
use Webkul\BagistoApi\Admin\State\AdminReturnReasonProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaCustomFieldWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsCurrencyWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportActionProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCancelProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportDownloadProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportStatsProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsDataTransferImportWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateUpdateRatesProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateExportProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxRateWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeMassDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeMassUpdateStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserDeleteSelfProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserWriteProvider;
use Webkul\BagistoApi\Admin\State\AdminShipmentCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminShipmentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminShipmentExportProvider;
use Webkul\BagistoApi\Admin\State\AdminShipmentProvider;
use Webkul\BagistoApi\Admin\State\AdminTransactionCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminTransactionCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminTransactionExportProvider;
use Webkul\BagistoApi\Admin\State\AdminTransactionItemProvider;
use Webkul\BagistoApi\Admin\State\OrderCollectionProvider;
use Webkul\BagistoApi\Admin\State\OrderDetailProvider;
use Webkul\BagistoApi\CacheProfiles\ApiAwareResponseCache;
use Webkul\BagistoApi\Console\Commands\ApiKeyMaintenanceCommand;
use Webkul\BagistoApi\Console\Commands\ApiKeyManagementCommand;
use Webkul\BagistoApi\Console\Commands\ClearApiPlatformCacheCommand;
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
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\BagistoApi\Metadata\CustomIdentifiersExtractor;
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
use Webkul\BagistoApi\State\AttributeOptionQueryProvider;
use Webkul\BagistoApi\State\AttributeValueProcessor;
use Webkul\BagistoApi\State\AuthenticatedCustomerProvider;
use Webkul\BagistoApi\State\BookingProductDetailProvider;
use Webkul\BagistoApi\State\BookingSlotProvider;
use Webkul\BagistoApi\State\BundleOptionProductsProvider;
use Webkul\BagistoApi\State\CancelOrderProcessor;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;
use Webkul\BagistoApi\State\CategoryRestProvider;
use Webkul\BagistoApi\State\CategoryTreeProvider;
use Webkul\BagistoApi\State\ChannelProvider;
use Webkul\BagistoApi\State\CheckoutAddressProvider;
use Webkul\BagistoApi\State\CheckoutProcessor;
use Webkul\BagistoApi\State\CompareItemItemProvider;
use Webkul\BagistoApi\State\CompareItemProcessor;
use Webkul\BagistoApi\State\CompareItemProvider;
use Webkul\BagistoApi\State\CountryStateCollectionProvider;
use Webkul\BagistoApi\State\CountryStateQueryProvider;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\BagistoApi\State\CustomerAddressItemProvider;
use Webkul\BagistoApi\State\CustomerAddressProvider;
use Webkul\BagistoApi\State\CustomerAddressTokenProcessor;
use Webkul\BagistoApi\State\CustomerDownloadableProductProvider;
use Webkul\BagistoApi\State\CustomerInvoiceProvider;
use Webkul\BagistoApi\State\CustomerOrderProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentItemProvider;
use Webkul\BagistoApi\State\CustomerOrderShipmentProvider;
use Webkul\BagistoApi\State\CustomerProcessor;
use Webkul\BagistoApi\State\CustomerProfileCollectionProvider;
use Webkul\BagistoApi\State\CustomerProfileProcessor;
use Webkul\BagistoApi\State\CustomerReturnMessageProcessor;
use Webkul\BagistoApi\State\CustomerReturnMessageProvider;
use Webkul\BagistoApi\State\CustomerReturnProcessor;
use Webkul\BagistoApi\State\CustomerReturnProvider;
use Webkul\BagistoApi\State\CustomerReviewProvider;
use Webkul\BagistoApi\State\CustomizableOptionFileProcessor;
use Webkul\BagistoApi\State\DefaultChannelProvider;
use Webkul\BagistoApi\State\DeleteAllCompareItemsProcessor;
use Webkul\BagistoApi\State\DeleteAllWishlistsProcessor;
use Webkul\BagistoApi\State\DownloadableLinksProvider;
use Webkul\BagistoApi\State\DownloadableProductProcessor;
use Webkul\BagistoApi\State\DownloadableSamplesProvider;
use Webkul\BagistoApi\State\EuWithdrawalProcessor;
use Webkul\BagistoApi\State\EuWithdrawalProvider;
use Webkul\BagistoApi\State\FilterableAttributesProvider;
use Webkul\BagistoApi\State\ForgotPasswordProcessor;
use Webkul\BagistoApi\State\GdprRequestItemProvider;
use Webkul\BagistoApi\State\GdprRequestProcessor;
use Webkul\BagistoApi\State\GdprRequestProvider;
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
use Webkul\BagistoApi\State\ProductRelationFlagResolver;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\BagistoApi\State\ProductRestProvider;
use Webkul\BagistoApi\State\ProductReviewProcessor;
use Webkul\BagistoApi\State\ProductReviewProvider;
use Webkul\BagistoApi\State\ReorderProcessor;
use Webkul\BagistoApi\State\ReturnableItemProvider;
use Webkul\BagistoApi\State\ReturnReasonProvider;
use Webkul\BagistoApi\State\ShippingRatesProvider;
use Webkul\BagistoApi\State\SnakeCaseLinksHandler;
use Webkul\BagistoApi\State\VerifyTokenProcessor;
use Webkul\BagistoApi\State\WishlistItemProvider;
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
     * Register the service provider bindings.
     */
    public function register(): void
    {
        $this->registerAdminApiGuardConfig();

        $this->mergeConfigFrom(__DIR__.'/../Admin/Config/audit.php', 'bagistoapi.audit');

        $this->mergeConfigFrom(__DIR__.'/../../config/storefront.php', 'storefront');

        $this->app->singleton(AdminApiAuditContext::class);
        $this->app->singleton(AdminApiAuditRecorder::class);

        // Force the API-aware response-cache profile. Spatie's default profile caches
        // every successful GET and hashes by path only, so paginated API responses
        // (?page=2, ?itemsPerPage=5) collapse onto one cache entry.
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

        $this->app->extend(
            PropertyMetadataFactoryInterface::class,
            function ($decorated) {
                return new NullableToOnePropertyMetadataFactory($decorated);
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

        $this->app->tag(ProductProcessor::class, ProcessorInterface::class);
        $this->app->tag(AttributeValueProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomizableOptionFileProcessor::class, ProcessorInterface::class);
        $this->app->tag(LoginProcessor::class, ProcessorInterface::class);
        $this->app->tag(VerifyTokenProcessor::class, ProcessorInterface::class);
        $this->app->tag(LogoutProcessor::class, ProcessorInterface::class);
        $this->app->tag(ForgotPasswordProcessor::class, ProcessorInterface::class);

        // Admin API — Profile read. Clients authenticate via admin integration
        // tokens (Bearer header → AdminApiGuard).
        $this->app->tag(AdminProfileProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerReturnProvider::class, ProviderInterface::class);
        $this->app->tag(EuWithdrawalProvider::class, ProviderInterface::class);
        $this->app->tag(ReturnableItemProvider::class, ProviderInterface::class);
        $this->app->tag(ReturnReasonProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerReturnMessageProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReturnCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReturnItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReturnableItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReturnReasonProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReturnProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminReturnMessageProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReturnMessageProcessor::class, ProcessorInterface::class);

        $this->app->singleton(AdminReturnMessageProcessor::class, function ($app) {
            return new AdminReturnMessageProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARepository::class),
                $app->make(RMAMessageRepository::class),
            );
        });

        $this->app->tag(AdminRmaReasonCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaReasonItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaReasonWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaReasonProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaReasonMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaReasonMassUpdateStatusProcessor::class, ProcessorInterface::class);

        $this->app->singleton(AdminRmaReasonProcessor::class, function ($app) {
            return new AdminRmaReasonProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMAReasonRepository::class),
                $app->make(RMAReasonResolutionRepository::class),
            );
        });

        $this->app->tag(AdminRmaStatusCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaStatusItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaStatusWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaStatusProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaStatusMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaStatusMassUpdateStatusProcessor::class, ProcessorInterface::class);

        $this->app->singleton(AdminRmaStatusProcessor::class, function ($app) {
            return new AdminRmaStatusProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMAStatusRepository::class),
            );
        });

        $this->app->tag(AdminRmaRuleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaRuleItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaRuleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaRuleProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaRuleMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaRuleMassUpdateStatusProcessor::class, ProcessorInterface::class);

        $this->app->singleton(AdminRmaRuleProcessor::class, function ($app) {
            return new AdminRmaRuleProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMARuleRepository::class),
            );
        });

        $this->app->tag(AdminRmaCustomFieldCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaCustomFieldItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaCustomFieldWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRmaCustomFieldProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaCustomFieldMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRmaCustomFieldMassUpdateStatusProcessor::class, ProcessorInterface::class);

        $this->app->singleton(AdminRmaCustomFieldProcessor::class, function ($app) {
            return new AdminRmaCustomFieldProcessor(
                $app->make(PersistProcessor::class),
                $app->make(RMACustomFieldRepository::class),
                $app->make(RMACustomFieldOptionRepository::class),
            );
        });

        $this->app->tag(AdminEuWithdrawalCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminEuWithdrawalItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminEuWithdrawalWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminEuWithdrawalProcessor::class, ProcessorInterface::class);

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
        $this->app->tag(CustomerReturnProcessor::class, ProcessorInterface::class);
        $this->app->tag(EuWithdrawalProcessor::class, ProcessorInterface::class);
        $this->app->tag(CustomerReturnMessageProcessor::class, ProcessorInterface::class);
        $this->app->tag(OrderCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(OrderDetailProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReorderProcessor::class, ProcessorInterface::class);

        // Admin Order Actions (Cancel / Comment / Invoice / Shipment / Refund).
        $this->app->tag(AdminCancelOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminOrderCommentCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminOrderCommentProvider::class, ProviderInterface::class);
        $this->app->tag(AdminInvoiceCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminTransactionCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminInvoiceSendDuplicateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminInvoiceMassUpdateStatusProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminInvoiceProvider::class, ProviderInterface::class);
        $this->app->tag(AdminInvoicePrintProvider::class, ProviderInterface::class);
        $this->app->tag(AdminShipmentCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminShipmentProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRefundCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRefundPreviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminRefundProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRefundExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminInvoiceExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminShipmentExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminTransactionExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminBookingExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminOrderExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxRateExportProvider::class, ProviderInterface::class);
        // Sales completion — datagrid listings + Transactions/Bookings detail
        $this->app->tag(AdminInvoiceCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminShipmentCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminRefundCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminTransactionCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminTransactionItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminBookingCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminBookingItemProvider::class, ProviderInterface::class);
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
        $this->app->tag(AdminAttributeFamilyProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminAttributeFamilyWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryTreeProvider::class, ProviderInterface::class);

        // Categories CRUD
        $this->app->tag(AdminCategoryProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCategoryWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCategoryMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCategoryMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Settings → Exchange Rates CRUD
        $this->app->tag(AdminSettingsExchangeRateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsExchangeRateItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsExchangeRateWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsExchangeRateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsExchangeRateMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsExchangeRateUpdateRatesProcessor::class, ProcessorInterface::class);

        // Settings → Tax Rates CRUD
        $this->app->tag(AdminSettingsTaxRateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxRateItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxRateWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxRateProcessor::class, ProcessorInterface::class);

        // Settings → Tax Categories CRUD
        $this->app->tag(AdminSettingsTaxCategoryCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxCategoryItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxCategoryWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsTaxCategoryProcessor::class, ProcessorInterface::class);

        // Marketing → Catalog Rules CRUD
        $this->app->tag(AdminMarketingCatalogRuleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCatalogRuleItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCatalogRuleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCatalogRuleProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingCatalogRuleMassDeleteProcessor::class, ProcessorInterface::class);

        // Marketing → Campaigns CRUD + send
        $this->app->tag(AdminMarketingCampaignCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCampaignItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCampaignWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCampaignProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingCampaignSendProcessor::class, ProcessorInterface::class);

        // Marketing → Sitemaps CRUD + generate
        $this->app->tag(AdminMarketingSitemapCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSitemapItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSitemapWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSitemapProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingSitemapGenerateProcessor::class, ProcessorInterface::class);

        // Marketing → Email Templates CRUD
        $this->app->tag(AdminMarketingTemplateCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingTemplateItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingTemplateWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingTemplateProcessor::class, ProcessorInterface::class);

        // Marketing → Events CRUD
        $this->app->tag(AdminMarketingEventCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingEventItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingEventWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingEventProcessor::class, ProcessorInterface::class);

        // Marketing → Search Synonyms CRUD
        $this->app->tag(AdminMarketingSearchSynonymCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSearchSynonymItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSearchSynonymWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSearchSynonymProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingSearchSynonymMassDeleteProcessor::class, ProcessorInterface::class);

        // Marketing → URL Rewrites CRUD
        $this->app->tag(AdminMarketingUrlRewriteCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingUrlRewriteItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingUrlRewriteWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingUrlRewriteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingUrlRewriteMassDeleteProcessor::class, ProcessorInterface::class);

        // Admin Customers CRUD + sub-resources
        $this->app->tag(AdminCustomerCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerMassUpdateStatusProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerAddressItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerAddressWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerAddressProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerNoteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerImpersonateProcessor::class, ProcessorInterface::class);

        // Admin Customer Groups CRUD
        $this->app->tag(AdminCustomerGroupCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerGroupItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerGroupWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerGroupProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerGroupMassDeleteProcessor::class, ProcessorInterface::class);

        // Admin Customer Reviews moderation
        $this->app->tag(AdminCustomerReviewProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerReviewWriteProvider::class, ProviderInterface::class);

        $this->app->tag(AdminCustomerReviewProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerReviewMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerReviewMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Admin Customer GDPR Requests
        $this->app->tag(AdminCustomerGdprCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerGdprItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerGdprWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCustomerGdprProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerGdprProcessProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCustomerGdprDownloadDataProcessor::class, ProcessorInterface::class);

        // Marketing → Cart Rules CRUD
        $this->app->tag(AdminMarketingCartRuleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCartRuleItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCartRuleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCartRuleProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingCartRuleCopyProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingCartRuleMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Locales CRUD
        $this->app->tag(AdminSettingsLocaleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsLocaleItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsLocaleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsLocaleProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsLocaleMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Themes (theme customizations) CRUD
        $this->app->tag(AdminSettingsThemeCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsThemeItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsThemeWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsThemeProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsThemeMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsThemeMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Settings → Users (admins) CRUD
        $this->app->tag(AdminSettingsUserCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsUserItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsUserWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsUserProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsUserDeleteSelfProcessor::class, ProcessorInterface::class);

        // Catalog Products mass actions
        $this->app->tag(AdminCatalogProductMassDeleteProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCatalogProductMassUpdateStatusProcessor::class, ProcessorInterface::class);

        // Catalog Product copy
        $this->app->tag(AdminCatalogProductCopyProcessor::class, ProcessorInterface::class);

        // Catalog Product create (simple)
        $this->app->tag(AdminCatalogProductCreateProcessor::class, ProcessorInterface::class);

        // Catalog Product update (any type)
        $this->app->tag(AdminCatalogProductUpdateProcessor::class, ProcessorInterface::class);

        // Catalog Product delete
        $this->app->tag(AdminCatalogProductDeleteProcessor::class, ProcessorInterface::class);

        // Catalog Product images (upload / reorder / delete)
        $this->app->tag(AdminCatalogProductImageProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCatalogProductImageProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductDownloadableFileProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCatalogProductDownloadableFileProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductVideoProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCatalogProductVideoProvider::class, ProviderInterface::class);

        // Admin Marketing Cart Rule Coupons (sub-resource of cart rules)
        $this->app->tag(AdminMarketingCartRuleCouponCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCartRuleCouponWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingCartRuleCouponProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingCartRuleCouponGenerateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingCartRuleCouponMassDeleteProcessor::class, ProcessorInterface::class);

        // Admin Marketing Newsletter Subscribers
        $this->app->tag(AdminMarketingSubscriberCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSubscriberItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSubscriberWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSubscriberProcessor::class, ProcessorInterface::class);

        // Admin Marketing Search Terms
        $this->app->tag(AdminMarketingSearchTermCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSearchTermItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSearchTermWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMarketingSearchTermProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminMarketingSearchTermMassDeleteProcessor::class, ProcessorInterface::class);

        // Catalog Product inventories (list + bulk update)
        $this->app->tag(AdminCatalogProductInventoryProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductInventoryProcessor::class, ProcessorInterface::class);

        // Catalog Product customer-group prices CRUD
        $this->app->tag(AdminCatalogProductCustomerGroupPriceProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCatalogProductCustomerGroupPriceProcessor::class, ProcessorInterface::class);

        // CMS Pages read-only + CRUD
        $this->app->tag(AdminCmsPageCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCmsPageItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCmsPageExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCmsPageWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminCmsPageProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminCmsPageMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Currencies CRUD
        $this->app->tag(AdminSettingsCurrencyCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsCurrencyItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsCurrencyWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsCurrencyProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsCurrencyMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Channels CRUD
        $this->app->tag(AdminSettingsChannelCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsChannelItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsChannelWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsChannelProcessor::class, ProcessorInterface::class);

        // Settings → Data Transfer Imports (list/detail/cancel/delete)
        $this->app->tag(AdminSettingsDataTransferImportCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportCancelProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportCreateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportActionProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportStatsProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsDataTransferImportDownloadProvider::class, ProviderInterface::class);

        // Settings → Inventory Sources CRUD
        $this->app->tag(AdminSettingsInventorySourceCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsInventorySourceItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsInventorySourceWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsInventorySourceProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminSettingsInventorySourceMassDeleteProcessor::class, ProcessorInterface::class);

        // Settings → Roles CRUD
        $this->app->tag(AdminSettingsRoleCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsRoleItemProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsRoleWriteProvider::class, ProviderInterface::class);
        $this->app->tag(AdminSettingsRoleProcessor::class, ProcessorInterface::class);

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
        $this->app->tag(GdprRequestProcessor::class, ProcessorInterface::class);
        $this->app->tag(MoveWishlistToCartProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllWishlistsProcessor::class, ProcessorInterface::class);
        $this->app->tag(DeleteAllCompareItemsProcessor::class, ProcessorInterface::class);
        $this->app->tag(CancelOrderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ReorderProcessor::class, ProcessorInterface::class);
        $this->app->tag(ContactUsProcessor::class, ProcessorInterface::class);

        $this->app->tag(TokenHeaderDenormalizer::class, 'serializer.normalizer');

        $this->app->extend('api_platform_normalizer_list', function (\SplPriorityQueue $list, $app) {
            $list->insert(
                $app->make(PaginationHeaderNormalizer::class),
                1000
            );

            // Higher priority than the header normalizer: wraps /api/admin/*
            // collection responses in the { data, meta } envelope.
            $list->insert(
                $app->make(AdminCollectionEnvelopeNormalizer::class),
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

        $this->app->singleton(EuWithdrawalProcessor::class, function ($app) {
            return new EuWithdrawalProcessor(
                $app->make(PersistProcessor::class),
                $app->make(OrderRepository::class),
                $app->make(WithdrawalService::class),
            );
        });

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

        $this->app->tag(CheckoutAddressProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerAddressProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerAddressItemProvider::class, ProviderInterface::class);
        $this->app->tag(GetCheckoutAddressCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PaymentMethodsProvider::class, ProviderInterface::class);
        $this->app->tag(ShippingRatesProvider::class, ProviderInterface::class);
        $this->app->tag(AuthenticatedCustomerProvider::class, ProviderInterface::class);
        $this->app->tag(CustomerProfileCollectionProvider::class, ProviderInterface::class);
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
        $this->app->tag(CategoryRestProvider::class, ProviderInterface::class);
        $this->app->tag(BookingSlotProvider::class, ProviderInterface::class);
        $this->app->tag(BookingProductDetailProvider::class, ProviderInterface::class);
        $this->app->tag(CursorAwareCollectionProvider::class, ProviderInterface::class);
        $this->app->tag(PageProvider::class, ProviderInterface::class);
        $this->app->tag(WishlistProvider::class, ProviderInterface::class);
        $this->app->tag(WishlistItemProvider::class, ProviderInterface::class);
        $this->app->tag(GdprRequestProvider::class, ProviderInterface::class);
        $this->app->tag(GdprRequestItemProvider::class, ProviderInterface::class);
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
        $this->app->tag(AdminDashboardProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingOverviewProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingSalesProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingCustomersProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingProductsProvider::class, ProviderInterface::class);
        $this->app->tag(AdminDashboardQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingOverviewQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingSalesQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingCustomersQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingProductsQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingSalesViewProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingCustomersViewProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingProductsViewProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingSalesExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingCustomersExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingProductsExportProvider::class, ProviderInterface::class);
        $this->app->tag(AdminReportingSalesViewResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingCustomersViewResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminReportingProductsViewResolver::class, QueryItemResolverInterface::class);

        // Admin Configuration (G1-G3) — shared schema resolver as singleton
        // so the system_config walk + flattened code→field map is built once
        // per request rather than once per endpoint hit.
        $this->app->singleton(AdminConfigurationSchemaResolver::class);
        $this->app->tag(AdminConfigurationMenuProvider::class, ProviderInterface::class);
        $this->app->tag(AdminConfigurationValuesProvider::class, ProviderInterface::class);
        $this->app->tag(AdminConfigurationUpdateProcessor::class, ProcessorInterface::class);
        $this->app->tag(AdminConfigurationSlugProvider::class, ProviderInterface::class);
        $this->app->tag(AdminConfigurationMenuQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminConfigurationValuesQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminConfigurationSlugQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminMenuProvider::class, ProviderInterface::class);
        $this->app->tag(AdminMenuQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminPermissionsProvider::class, ProviderInterface::class);
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
            $this->app->singleton(EntrypointController::class, function ($app) use ($scopedEntrypoint) {
                return $scopedEntrypoint($app, false);
            });
        }

        // The admin `/api/admin/graphql` route uses our own wrapper controller, bound
        // with the ADMIN-scoped schema (excludes storefront resources).
        $this->app->singleton(AdminGraphQLEntrypointController::class, function ($app) use ($scopedEntrypoint) {
            return new AdminGraphQLEntrypointController(
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

        // Dedicated admin GraphQL endpoint. Same API Platform handler/schema as
        // /api/graphql, but with admin Bearer auth (EnforceAdminApiAuth) instead
        // of the storefront key (VerifyGraphQLStorefrontKey). No back door — the
        // shop endpoint does not accept admin Bearer tokens, and this endpoint
        // does not accept storefront keys.
        Route::post(
            '/api/admin/graphql',
            AdminGraphQLEntrypointController::class
        )
            ->middleware([
                EnforceAdminApiAuth::class,
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
        $this->app['router']->aliasMiddleware('api.locale-channel', SetLocaleChannel::class);
        $this->app['router']->aliasMiddleware('api.rate-limit', RateLimitApi::class);
        $this->app['router']->aliasMiddleware('api.security-headers', SecurityHeaders::class);
        $this->app['router']->aliasMiddleware('api.log-requests', LogApiRequests::class);
    }

    /**
     * Register global middleware that runs on every HTTP request.
     * EnsureJsonContentType lets bodyless POST endpoints (e.g., delete-all-*)
     * work without clients needing to send a Content-Type header.
     */
    protected function registerGlobalMiddleware(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(EnsureJsonContentType::class);
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
            InstallApiPlatformCommand::class,
            ClearApiPlatformCacheCommand::class,
            WarmApiPlatformCacheCommand::class,
            OptimizeApiPlatformCommand::class,
            GenerateStorefrontKey::class,
            ApiKeyManagementCommand::class,
            ApiKeyMaintenanceCommand::class,
            PruneAuditsCommand::class,
            PruneCartUploadsCommand::class,
        ]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('bagisto-api:prune-cart-uploads')->everyTwoHours();
        });
    }

    /**
     * Override API Platform's ItemProvider and CollectionProvider to wrap the
     * LinksHandler with SnakeCaseLinksHandler, fixing the camelCase/snake_case
     * mismatch between GraphQL field names and Eloquent relationship names.
     */
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
            $token = method_exists($request, 'user') ? $request->user('admin-api')?->getAttribute('current_access_token') : null;

            if (! $token instanceof AdminPersonalAccessToken) {
                return Limit::perMinute(60)->by($request->ip());
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
