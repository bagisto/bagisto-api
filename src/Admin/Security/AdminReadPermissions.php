<?php

namespace Webkul\BagistoApi\Admin\Security;

use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSection;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionFields;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceSectionPreview;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceTheme;
use Webkul\BagistoApi\Admin\Models\AdminAppearanceThemeImpact;
use Webkul\BagistoApi\Admin\Models\AdminAttribute;
use Webkul\BagistoApi\Admin\Models\AdminAttributeFamily;
use Webkul\BagistoApi\Admin\Models\AdminAttributeOption;
use Webkul\BagistoApi\Admin\Models\AdminBooking;
use Webkul\BagistoApi\Admin\Models\AdminCart;
use Webkul\BagistoApi\Admin\Models\AdminCartPaymentMethod;
use Webkul\BagistoApi\Admin\Models\AdminCartShippingRate;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductCustomerGroupPrice;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductDownloadableFile;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductImage;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductInventory;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductVideo;
use Webkul\BagistoApi\Admin\Models\AdminCategory;
use Webkul\BagistoApi\Admin\Models\AdminCategoryTree;
use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationMenu;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSlug;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationValues;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\Models\AdminCustomerAddress;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCartItem;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCompareItem;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGroup;
use Webkul\BagistoApi\Admin\Models\AdminCustomerNote;
use Webkul\BagistoApi\Admin\Models\AdminCustomerRecentOrderItem;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\Models\AdminCustomerWishlistItem;
use Webkul\BagistoApi\Admin\Models\AdminDashboard;
use Webkul\BagistoApi\Admin\Models\AdminEuWithdrawal;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleCoupon;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCatalogRule;
use Webkul\BagistoApi\Admin\Models\AdminMarketingEvent;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchSynonym;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSearchTerm;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemap;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Admin\Models\AdminMarketingTemplate;
use Webkul\BagistoApi\Admin\Models\AdminMarketingUrlRewrite;
use Webkul\BagistoApi\Admin\Models\AdminMenu;
use Webkul\BagistoApi\Admin\Models\AdminOrder;
use Webkul\BagistoApi\Admin\Models\AdminOrderComment;
use Webkul\BagistoApi\Admin\Models\AdminPermissions;
use Webkul\BagistoApi\Admin\Models\AdminProduct;
use Webkul\BagistoApi\Admin\Models\AdminProfile;
use Webkul\BagistoApi\Admin\Models\AdminRefund;
use Webkul\BagistoApi\Admin\Models\AdminReportingCustomers;
use Webkul\BagistoApi\Admin\Models\AdminReportingOverview;
use Webkul\BagistoApi\Admin\Models\AdminReportingProducts;
use Webkul\BagistoApi\Admin\Models\AdminReportingSales;
use Webkul\BagistoApi\Admin\Models\AdminReturn;
use Webkul\BagistoApi\Admin\Models\AdminReturnableItem;
use Webkul\BagistoApi\Admin\Models\AdminReturnableOrder;
use Webkul\BagistoApi\Admin\Models\AdminReturnFile;
use Webkul\BagistoApi\Admin\Models\AdminReturnMessage;
use Webkul\BagistoApi\Admin\Models\AdminReturnReason;
use Webkul\BagistoApi\Admin\Models\AdminRmaCustomField;
use Webkul\BagistoApi\Admin\Models\AdminRmaReason;
use Webkul\BagistoApi\Admin\Models\AdminRmaRule;
use Webkul\BagistoApi\Admin\Models\AdminRmaStatus;
use Webkul\BagistoApi\Admin\Models\AdminSession;
use Webkul\BagistoApi\Admin\Models\AdminSettingsApiSession;
use Webkul\BagistoApi\Admin\Models\AdminSettingsApiToken;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Admin\Models\AdminSettingsCurrency;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportDownload;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportStats;
use Webkul\BagistoApi\Admin\Models\AdminSettingsExchangeRate;
use Webkul\BagistoApi\Admin\Models\AdminSettingsInventorySource;
use Webkul\BagistoApi\Admin\Models\AdminSettingsLocale;
use Webkul\BagistoApi\Admin\Models\AdminSettingsRole;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxRate;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTheme;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUser;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUserSession;
use Webkul\BagistoApi\Admin\Models\AdminShipment;
use Webkul\BagistoApi\Admin\Models\AdminTransaction;
use Webkul\BagistoApi\Admin\Models\OrderDetail;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminReadPermissions
{
    /**
     * The core ACL key of each admin resource class, or null for the classes any signed-in admin may read.
     *
     * @var array<class-string, string|null>
     */
    public const MAP = [
        AdminProfile::class => null,
        AdminMenu::class => null,
        AdminPermissions::class => null,
        AdminSession::class => null,

        AdminDashboard::class => 'dashboard',

        AdminOrder::class => 'sales.orders',
        OrderDetail::class => 'sales.orders.view',
        AdminOrderComment::class => 'sales.orders.view',
        AdminCart::class => 'sales.orders.create',
        AdminCartPaymentMethod::class => 'sales.orders.create',
        AdminCartShippingRate::class => 'sales.orders.create',
        AdminProduct::class => 'sales.orders.create',
        AdminCustomerCartItem::class => 'sales.orders.create',
        AdminCustomerCompareItem::class => 'sales.orders.create',
        AdminCustomerWishlistItem::class => 'sales.orders.create',
        AdminCustomerRecentOrderItem::class => 'sales.orders.create',
        AdminInvoice::class => 'sales.invoices',
        AdminShipment::class => 'sales.shipments',
        AdminRefund::class => 'sales.refunds',
        AdminTransaction::class => 'sales.transactions',
        AdminBooking::class => 'sales.bookings',
        AdminEuWithdrawal::class => 'sales.eu_withdrawals',
        AdminReturn::class => 'sales.rma.requests',
        AdminReturnableItem::class => 'sales.rma.requests',
        AdminReturnableOrder::class => 'sales.rma.requests.create',
        AdminReturnFile::class => 'sales.rma.requests',
        AdminReturnMessage::class => 'sales.rma.requests',
        AdminReturnReason::class => 'sales.rma.requests',
        AdminRmaReason::class => 'sales.rma.reasons',
        AdminRmaRule::class => 'sales.rma.rules',
        AdminRmaStatus::class => 'sales.rma.statuses',
        AdminRmaCustomField::class => 'sales.rma.custom-fields',

        AdminCatalogProduct::class => 'catalog.products',
        AdminCatalogProductCustomerGroupPrice::class => 'catalog.products',
        AdminCatalogProductDownloadableFile::class => 'catalog.products',
        AdminCatalogProductImage::class => 'catalog.products',
        AdminCatalogProductInventory::class => 'catalog.products',
        AdminCatalogProductVideo::class => 'catalog.products',
        AdminCategory::class => 'catalog.categories',
        AdminCategoryTree::class => 'catalog.categories',
        AdminAttribute::class => 'catalog.attributes',
        AdminAttributeOption::class => 'catalog.attributes',
        AdminAttributeFamily::class => 'catalog.families',

        AdminCustomer::class => 'customers.customers',
        AdminCustomerAddress::class => 'customers.addresses',
        AdminCustomerNote::class => 'customers.note',
        AdminCustomerGroup::class => 'customers.groups',
        AdminCustomerReview::class => 'customers.reviews',
        AdminCustomerGdprRequest::class => 'customers.gdpr_requests',

        AdminMarketingCartRule::class => 'marketing.promotions.cart_rules',
        AdminMarketingCartRuleCoupon::class => 'marketing.promotions.cart_rules',
        AdminMarketingCatalogRule::class => 'marketing.promotions.catalog_rules',
        AdminMarketingTemplate::class => 'marketing.communications.email_templates',
        AdminMarketingEvent::class => 'marketing.communications.events',
        AdminMarketingCampaign::class => 'marketing.communications.campaigns',
        AdminMarketingSubscriber::class => 'marketing.communications.subscribers',
        AdminMarketingUrlRewrite::class => 'marketing.search_seo.url_rewrites',
        AdminMarketingSearchTerm::class => 'marketing.search_seo.search_terms',
        AdminMarketingSearchSynonym::class => 'marketing.search_seo.search_synonyms',
        AdminMarketingSitemap::class => 'marketing.search_seo.sitemaps',

        AdminReportingOverview::class => 'reporting',
        AdminReportingSales::class => 'reporting.sales',
        AdminReportingCustomers::class => 'reporting.customers',
        AdminReportingProducts::class => 'reporting.products',

        AdminCmsPage::class => 'cms',

        AdminSettingsLocale::class => 'settings.locales',
        AdminSettingsCurrency::class => 'settings.currencies',
        AdminSettingsExchangeRate::class => 'settings.exchange_rates',
        AdminSettingsInventorySource::class => 'settings.inventory_sources',
        AdminSettingsChannel::class => 'settings.channels',
        AdminSettingsUser::class => 'settings.users',
        AdminSettingsRole::class => 'settings.roles',
        AdminSettingsUserSession::class => 'settings.api_sessions',
        AdminSettingsApiSession::class => 'settings.api_sessions',
        AdminSettingsApiToken::class => 'settings.api_tokens',
        AdminSettingsTaxCategory::class => 'settings.taxes.tax_categories',
        AdminSettingsTaxRate::class => 'settings.taxes.tax_rates',
        AdminSettingsTheme::class => 'settings.themes',
        AdminSettingsDataTransferImport::class => 'settings.data_transfer.imports',
        AdminSettingsDataTransferImportDownload::class => 'settings.data_transfer.imports.import',
        AdminSettingsDataTransferImportStats::class => 'settings.data_transfer.imports.import',

        AdminConfigurationMenu::class => 'configuration',
        AdminConfigurationSlug::class => 'configuration',
        AdminConfigurationValues::class => 'configuration',

        AdminAppearanceTheme::class => 'appearance.themes',
        AdminAppearanceThemeImpact::class => 'appearance.themes',
        AdminAppearanceSection::class => 'appearance.sections',
        AdminAppearanceSectionFields::class => 'appearance.sections',
        AdminAppearanceSectionPreview::class => 'appearance.sections',
    ];

    /**
     * Resolve the calling admin and enforce the read permission of the resource.
     *
     * @throws AuthenticationException When no admin is resolved (401).
     * @throws AuthorizationException When the admin's role lacks the key, or the resource has no mapped key (403).
     */
    public static function authorize(?Operation $operation = null, ?string $fallbackClass = null, ?string $override = null): object
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $permission = $override ?? self::permissionFor($operation?->getClass() ?? $fallbackClass);

        if (
            $permission !== null
            && ! AdminAuthHelper::hasPermission($admin, $permission)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }

        return $admin;
    }

    /**
     * The ACL key for a resource class, or null when the class is explicitly mapped to authentication only.
     *
     * @throws AuthorizationException When the class has no entry in the map, so a new resource is closed by default.
     */
    public static function permissionFor(?string $resourceClass): ?string
    {
        if (
            $resourceClass === null
            || ! array_key_exists($resourceClass, self::MAP)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }

        return self::MAP[$resourceClass];
    }
}
