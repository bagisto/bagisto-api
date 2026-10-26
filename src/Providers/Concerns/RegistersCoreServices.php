<?php

namespace Webkul\BagistoApi\Providers\Concerns;

use ApiPlatform\GraphQl\Type\Definition\IterableType;
use ApiPlatform\Laravel\Eloquent\Metadata\ModelMetadata;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditContext;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;
use Webkul\BagistoApi\Admin\Metadata\NullableToOnePropertyMetadataFactory;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Admin\Services\DnsDomainResolver;
use Webkul\BagistoApi\Admin\Services\DomainResolver;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtKeyStore;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtVerifier;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Metadata\DatabaseIndependentColumnTypePropertyMetadataFactory;
use Webkul\BagistoApi\Metadata\PathGatedResourceNameCollectionFactory;
use Webkul\BagistoApi\Metadata\SourceDocblockPropertyMetadataFactory;
use Webkul\BagistoApi\Metadata\VersionGatedResourceNameCollectionFactory;
use Webkul\BagistoApi\OpenApi\SplitOpenApiFactory;
use Webkul\BagistoApi\Providers\ApiStateBindingsServiceProvider;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Serializer\AdminCollectionEnvelopeNormalizer;
use Webkul\BagistoApi\Serializer\PaginationHeaderNormalizer;
use Webkul\BagistoApi\Serializer\TokenHeaderDenormalizer;
use Webkul\BagistoApi\Services\CartTokenService;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthSettings;
use Webkul\BagistoApi\Services\StorefrontKeyService;
use Webkul\BagistoApi\Services\TokenHeaderService;
use Webkul\BagistoApi\Support\CoreCapabilities;

trait RegistersCoreServices
{
    /**
     * Register core services, config merges and container decorators.
     */
    protected function registerCoreBindings(): void
    {
        $this->app->singleton(CoreCapabilities::class);

        $this->registerAdminApiGuardConfig();

        $this->mergeConfigFrom(__DIR__.'/../../Admin/Config/audit.php', 'bagistoapi.audit');

        $this->mergeConfigFrom(__DIR__.'/../../Admin/Config/admin-auth.php', 'bagistoapi.admin_auth');

        $this->mergeConfigFrom(__DIR__.'/../../Config/customer-auth.php', 'bagistoapi.customer_auth');

        $this->mergeConfigFrom(__DIR__.'/../../../config/storefront.php', 'storefront');

        $this->app->singleton(AdminApiAuditContext::class);
        $this->app->singleton(AdminApiAuditRecorder::class);

        $this->app->singleton(AdminApiAuthSettings::class);
        $this->app->singleton(CustomerApiAuthSettings::class);
        $this->app->singleton(JwtKeyStore::class);
        $this->app->singleton(JwtSigner::class);
        $this->app->singleton(JwtVerifier::class);
        $this->app->singleton(DomainResolver::class, DnsDomainResolver::class);

        $this->registerApiAwareCacheProfile();

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

        $this->app->extend(ResourceNameCollectionFactoryInterface::class, function ($inner, $app) {
            return new VersionGatedResourceNameCollectionFactory($inner, $app->make(CoreCapabilities::class));
        });

        $this->app->extend(ResourceNameCollectionFactoryInterface::class, function ($inner) {
            return new PathGatedResourceNameCollectionFactory($inner);
        });

        $this->app->extend(
            PropertyMetadataFactoryInterface::class,
            function ($decorated, $app) {
                return new DatabaseIndependentColumnTypePropertyMetadataFactory($decorated, $app->make(ModelMetadata::class));
            }
        );

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
                $app->make('Webkul\BagistoApi\Repositories\GuestCartTokensRepository')
            );
        });

        $this->app->alias('cart-token-service', CartTokenFacade::class);

        $this->app->singleton('Webkul\BagistoApi\Repositories\GuestCartTokensRepository', function ($app) {
            return new GuestCartTokensRepository($app);
        });

        $this->app->register(ApiStateBindingsServiceProvider::class);
    }

    /**
     * Register the serializer normalizers and the response envelope.
     */
    protected function registerSerializerNormalizers(): void
    {
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
    }
}
