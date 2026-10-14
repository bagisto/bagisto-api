<?php

namespace Webkul\BagistoApi\Providers\Concerns;

use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use ApiPlatform\GraphQl\Serializer\SerializerContextBuilder as GraphQlSerializerContextBuilder;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceSectionFieldsQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceSectionPreviewQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceThemeImpactQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceThemeQueryResolver;
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
use Webkul\BagistoApi\GraphQl\Serializer\FixedSerializerContextBuilder;
use Webkul\BagistoApi\Metadata\CustomIdentifiersExtractor;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\Resolver\CategoryCollectionResolver;
use Webkul\BagistoApi\Resolver\CompareItemQueryResolver;
use Webkul\BagistoApi\Resolver\CustomerQueryResolver;
use Webkul\BagistoApi\Resolver\EuWithdrawalStatusQueryResolver;
use Webkul\BagistoApi\Resolver\Factory\ProductRelationResolverFactory;
use Webkul\BagistoApi\Resolver\GdprRequestQueryResolver;
use Webkul\BagistoApi\Resolver\GdprStatusQueryResolver;
use Webkul\BagistoApi\Resolver\PageByUrlKeyResolver;
use Webkul\BagistoApi\Resolver\ProductCollectionResolver;
use Webkul\BagistoApi\Resolver\SingleProductBagistoApiResolver;
use Webkul\BagistoApi\Resolver\StoreConfigQueryResolver;
use Webkul\BagistoApi\Resolver\ThemeQueryResolver;
use Webkul\BagistoApi\Resolver\WishlistQueryResolver;
use Webkul\BagistoApi\Routing\CustomIriConverter;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\BagistoApi\Support\CoreCapabilities;

trait RegistersGraphQlServices
{
    /**
     * Tag the GraphQL query resolvers.
     */
    protected function registerGraphQlResolvers(): void
    {
        $this->app->singleton(ProductCollectionResolver::class);
        $this->app->tag(SingleProductBagistoApiResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CategoryCollectionResolver::class, QueryCollectionResolverInterface::class);
        $this->app->tag(BaseQueryItemResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(CompareItemQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(WishlistQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(GdprStatusQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(StoreConfigQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(EuWithdrawalStatusQueryResolver::class, QueryItemResolverInterface::class);
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
        // These resolvers reach the section repository through their providers, and every
        // tagged resolver is instantiated when the schema is built.
        //
        // BACKWARD COMPATIBILITY: unwrap this condition when the minimum supported core is
        // 2.4.10. The older theme surface has no resolvers — it uses BaseQueryItemResolver.
        if (app(CoreCapabilities::class)->hasAppearanceSections()) {
            $this->app->tag(ThemeQueryResolver::class, QueryItemResolverInterface::class);
            $this->app->tag(AdminAppearanceThemeQueryResolver::class, QueryItemResolverInterface::class);
            $this->app->tag(AdminAppearanceThemeImpactQueryResolver::class, QueryItemResolverInterface::class);
            $this->app->tag(AdminAppearanceSectionFieldsQueryResolver::class, QueryItemResolverInterface::class);
            $this->app->tag(AdminAppearanceSectionPreviewQueryResolver::class, QueryItemResolverInterface::class);
        }
        $this->app->tag(AdminConfigurationValuesQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminConfigurationSlugQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminMenuQueryResolver::class, QueryItemResolverInterface::class);
        $this->app->tag(AdminPermissionsQueryResolver::class, QueryItemResolverInterface::class);

        $this->app->tag(PageByUrlKeyResolver::class, QueryCollectionResolverInterface::class);
    }

    /**
     * Decorate the GraphQL resolver, IRI and serializer services.
     */
    protected function registerGraphQlDecorators(): void
    {
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
}
