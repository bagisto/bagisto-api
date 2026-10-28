<?php

namespace Webkul\BagistoApi\Providers;

use ApiPlatform\GraphQl\Error\ErrorHandlerInterface;
use ApiPlatform\GraphQl\ExecutorInterface;
use ApiPlatform\GraphQl\Type\FieldsBuilderEnumInterface;
use ApiPlatform\GraphQl\Type\TypesContainerInterface;
use ApiPlatform\GraphQl\Type\TypesFactoryInterface;
use ApiPlatform\Laravel\Eloquent\State\CollectionProvider;
use ApiPlatform\Laravel\Eloquent\State\ItemProvider;
use ApiPlatform\Laravel\Eloquent\State\LinksHandler;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\QueryExtensionInterface;
use ApiPlatform\Laravel\GraphQl\Controller\EntrypointController;
use ApiPlatform\Laravel\GraphQl\Controller\GraphiQlController;
use ApiPlatform\Laravel\ServiceLocator;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\State\Pagination\Pagination;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Negotiation\Negotiator;
use Symfony\Component\Serializer\SerializerInterface;
use Webkul\Attribute\Contracts\Attribute as AttributeContract;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;
use Webkul\BagistoApi\Admin\Auth\AdminApiAuthenticator;
use Webkul\BagistoApi\Admin\Auth\AdminApiGuard;
use Webkul\BagistoApi\Admin\Contracts\AdminApiAudit as AdminApiAuditContract;
use Webkul\BagistoApi\Admin\Contracts\AdminApiRefreshToken as AdminApiRefreshTokenContract;
use Webkul\BagistoApi\Admin\Contracts\AdminApiSession as AdminApiSessionContract;
use Webkul\BagistoApi\Admin\Contracts\AdminApiToken as AdminApiTokenContract;
use Webkul\BagistoApi\Admin\Contracts\AdminApiTwoFactorChallenge as AdminApiTwoFactorChallengeContract;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiRefreshToken;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;
use Webkul\BagistoApi\Admin\Observers\AdminCredentialObserver;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthSettings;
use Webkul\BagistoApi\Admin\Services\AdminApiCredentialRevoker;
use Webkul\BagistoApi\Auth\CustomerApiAuthenticator;
use Webkul\BagistoApi\Auth\CustomerApiGuard;
use Webkul\BagistoApi\CacheProfiles\ApiAwareResponseCache;
use Webkul\BagistoApi\Console\Commands\ApiKeyMaintenanceCommand;
use Webkul\BagistoApi\Console\Commands\ApiKeyManagementCommand;
use Webkul\BagistoApi\Console\Commands\ApiTokenCreateCommand;
use Webkul\BagistoApi\Console\Commands\ClearApiPlatformCacheCommand;
use Webkul\BagistoApi\Console\Commands\ExportSchemaCommand;
use Webkul\BagistoApi\Console\Commands\GenerateStorefrontKey;
use Webkul\BagistoApi\Console\Commands\InstallApiPlatformCommand;
use Webkul\BagistoApi\Console\Commands\JwtKeysCommand;
use Webkul\BagistoApi\Console\Commands\OptimizeApiPlatformCommand;
use Webkul\BagistoApi\Console\Commands\PruneAdminTokensCommand;
use Webkul\BagistoApi\Console\Commands\PruneAuditsCommand;
use Webkul\BagistoApi\Console\Commands\PruneCartUploadsCommand;
use Webkul\BagistoApi\Console\Commands\PruneCustomerTokensCommand;
use Webkul\BagistoApi\Console\Commands\WarmApiPlatformCacheCommand;
use Webkul\BagistoApi\Contracts\CustomerApiRefreshToken as CustomerApiRefreshTokenContract;
use Webkul\BagistoApi\Contracts\CustomerApiSession as CustomerApiSessionContract;
use Webkul\BagistoApi\GraphQl\QueryScopedSchemaBuilder;
use Webkul\BagistoApi\GraphQl\ScopedSchemaBuilder;
use Webkul\BagistoApi\Http\Controllers\AdminGraphQLEntrypointController;
use Webkul\BagistoApi\Http\Controllers\AdminGraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\ApiEntrypointController;
use Webkul\BagistoApi\Http\Controllers\DownloadablePurchasedController;
use Webkul\BagistoApi\Http\Controllers\DownloadSampleController;
use Webkul\BagistoApi\Http\Controllers\GdprDataPdfController;
use Webkul\BagistoApi\Http\Controllers\GraphQLPlaygroundController;
use Webkul\BagistoApi\Http\Controllers\InvoicePdfController;
use Webkul\BagistoApi\Http\Controllers\ReturnMessageAttachmentController;
use Webkul\BagistoApi\Http\Controllers\SwaggerUIController;
use Webkul\BagistoApi\Http\Middleware\EnforceAdminApiAuth;
use Webkul\BagistoApi\Http\Middleware\EnforceCustomerApiAuth;
use Webkul\BagistoApi\Http\Middleware\EnsureJsonContentType;
use Webkul\BagistoApi\Http\Middleware\LogApiRequests;
use Webkul\BagistoApi\Http\Middleware\SecurityHeaders;
use Webkul\BagistoApi\Http\Middleware\SetAdminApiAuditContext;
use Webkul\BagistoApi\Http\Middleware\SetLocaleChannel;
use Webkul\BagistoApi\Http\Middleware\ThrottleAdminApi;
use Webkul\BagistoApi\Http\Middleware\ThrottleCustomerApi;
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\BagistoApi\Models\CoreAttribute;
use Webkul\BagistoApi\Models\CustomerApiRefreshToken;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Observers\CustomerCredentialObserver;
use Webkul\BagistoApi\Providers\Concerns\RegistersAdminProcessors;
use Webkul\BagistoApi\Providers\Concerns\RegistersCoreServices;
use Webkul\BagistoApi\Providers\Concerns\RegistersGraphQlServices;
use Webkul\BagistoApi\Providers\Concerns\RegistersStorefrontState;
use Webkul\BagistoApi\State\SnakeCaseLinksHandler;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Core\Helpers\Locales;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\EUWithdrawal\Services\WithdrawalService;
use Webkul\FPC\CacheProfiles\FullPageCacheProfile;
use Webkul\User\Models\AdminProxy;

class BagistoApiServiceProvider extends ServiceProvider
{
    use RegistersAdminProcessors;
    use RegistersCoreServices;
    use RegistersGraphQlServices;
    use RegistersStorefrontState;

    /**
     * The admin auth model contracts and the models that implement them.
     */
    private const AUTH_MODELS = [
        AdminApiSessionContract::class => AdminApiSession::class,
        AdminApiRefreshTokenContract::class => AdminApiRefreshToken::class,
        AdminApiTokenContract::class => AdminApiToken::class,
        AdminApiTwoFactorChallengeContract::class => AdminApiTwoFactorChallenge::class,
        AdminApiAuditContract::class => AdminApiAudit::class,
        CustomerApiSessionContract::class => CustomerApiSession::class,
        CustomerApiRefreshTokenContract::class => CustomerApiRefreshToken::class,
    ];

    /**
     * Package version, surfaced as the OpenAPI `info.version`.
     */
    const BAGISTO_API_VERSION = '2.5.0';

    /**
     * Register the service provider bindings.
     */
    public function register(): void
    {
        $this->registerCoreBindings();
        $this->registerAdminReturnProcessors();
        $this->registerSerializerNormalizers();
        $this->registerStorefrontProcessors();
        $this->registerStorefrontProviders();
        $this->registerGraphQlResolvers();
        $this->registerGraphQlDecorators();
        $this->registerAuthModelContracts();

        $this->app->scoped(AdminApiCredentialRevoker::class);
        $this->app->scoped(Locales::class);
    }

    /**
     * Bootstrap translations, migrations, views, publishable assets, routes, middleware and commands.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'bagistoapi');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webkul');

        $this->app->booted(function () {
            $this->registerModelSubstitutions();

            AdminProxy::modelClass()::observe(AdminCredentialObserver::class);

            CustomerProxy::modelClass()::observe(CustomerCredentialObserver::class);
        });

        $this->bootAdminPanel();

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
     * Bind the storefront and admin GraphQL entrypoints to schemas scoped to their own resources.
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

    /**
     * Substitute the API attribute model for the core attribute contract and register the admin auth models.
     * Runs once every provider has booted, so core's own Concord registrations cannot override it.
     */
    protected function registerModelSubstitutions(): void
    {
        if (! $this->app->bound('concord')) {
            return;
        }

        $models = [AttributeContract::class => CoreAttribute::class] + self::AUTH_MODELS;

        foreach ($models as $contract => $model) {
            try {
                $this->app->make('concord')->registerModel($contract, $model);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    /**
     * Bind the admin auth model contracts before any provider boots, since API Platform builds their repositories while booting.
     */
    protected function registerAuthModelContracts(): void
    {
        foreach (self::AUTH_MODELS as $contract => $model) {
            $this->app->bind($contract, $model);
        }
    }

    /**
     * Register API documentation routes.
     */
    protected function registerApiDocumentationRoutes(): void
    {
        Route::get('/api', ApiEntrypointController::class)
            ->name('bagistoapi.docs_index');

        Route::get('/api/shop', [
            SwaggerUIController::class, 'shopApi',
        ])->name('bagistoapi.shop_docs')->where('_format', '^(?!json|xml|csv)');

        Route::get('/api/admin', [
            SwaggerUIController::class, 'adminApi',
        ])->name('bagistoapi.admin_docs')->where('_format', '^(?!json|xml|csv)');

        Route::get('/api/shop/docs', [
            SwaggerUIController::class, 'shopApiDocs',
        ])->name('bagistoapi.shop_api_spec');

        Route::get('/api/admin/docs', [
            SwaggerUIController::class, 'adminApiDocs',
        ])->name('bagistoapi.admin_api_spec');

        Route::get('/api/graphiql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.graphql_playground');

        Route::get('/api/graphql', GraphQLPlaygroundController::class)
            ->name('bagistoapi.api_graphql_playground');

        Route::get('/api/admin/graphiql', AdminGraphQLPlaygroundController::class)
            ->name('bagistoapi.admin_graphql_playground');

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
            ->name('bagistoapi.admin_api_graphql');

        Route::get('/api/shop/customer-invoices/{id}/pdf', InvoicePdfController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer_invoice_pdf');

        Route::get('/api/shop/gdpr-data/pdf', GdprDataPdfController::class)
            ->middleware(['Webkul\\BagistoApi\\Http\\Middleware\\VerifyStorefrontKey'])
            ->name('bagistoapi.customer_gdpr_data_pdf');

        Route::get('/api/downloadable/download-sample/{type}/{id}', DownloadSampleController::class)
            ->where('type', 'link|sample')
            ->where('id', '[0-9]+')
            ->name('bagistoapi.download_sample');

        Route::get('/api/shop/customer-downloadable-products/{id}/download', DownloadablePurchasedController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer_downloadable_product_download');

        Route::get('/api/shop/return-messages/{id}/attachment', ReturnMessageAttachmentController::class)
            ->where('id', '[0-9]+')
            ->middleware(['Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey'])
            ->name('bagistoapi.customer_return_message_attachment');
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
     * Determine whether the EU withdrawal package is installed.
     */
    protected function isEuWithdrawalAvailable(): bool
    {
        return class_exists(WithdrawalService::class);
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddlewareAliases(): void
    {
        $this->app['router']->aliasMiddleware('storefront.key', VerifyStorefrontKey::class);
        $this->app['router']->aliasMiddleware('api.locale-channel', SetLocaleChannel::class);
        $this->app['router']->aliasMiddleware('api.security-headers', SecurityHeaders::class);
        $this->app['router']->aliasMiddleware('api.log-requests', LogApiRequests::class);
    }

    /**
     * Add the JSON content type, customer token and customer throttle middleware to the global HTTP kernel stack.
     */
    protected function registerGlobalMiddleware(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(EnsureJsonContentType::class);

        $kernel->pushMiddleware(EnforceCustomerApiAuth::class);

        $kernel->pushMiddleware(ThrottleCustomerApi::class);
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
     * Swap in the API-aware page-cache profile, only where core's own profile is the one configured.
     */
    protected function registerApiAwareCacheProfile(): void
    {
        if (! class_exists(FullPageCacheProfile::class)) {
            return;
        }

        if (config('responsecache.cache_profile') !== FullPageCacheProfile::class) {
            return;
        }

        config(['responsecache.cache_profile' => ApiAwareResponseCache::class]);
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
            JwtKeysCommand::class,
            ApiTokenCreateCommand::class,
            PruneAdminTokensCommand::class,
            PruneCustomerTokensCommand::class,
            ExportSchemaCommand::class,
        ]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('bagisto-api:prune-cart-uploads')->everyTwoHours();

            $schedule->command('bagisto-api:prune-admin-tokens')->hourly();

            $schedule->command('bagisto-api:prune-customer-tokens')->hourly();

            $schedule->command('bagisto-api:prune-audits')->daily();
        });
    }

    /**
     * Rebuild the Eloquent item and collection providers around the snake_case links handler.
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
     * Merge the admin API guard into the auth guards configuration.
     */
    protected function registerAdminApiGuardConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Admin/Config/auth/guards.php',
            'auth.guards'
        );
    }

    /**
     * Merge the package ACL and system configuration into core's.
     */
    protected function mergeAdminConfigs(): void
    {
        $aclConfig = require __DIR__.'/../Admin/Config/acl.php';
        $existingAcl = (array) config('acl', []);
        config(['acl' => array_merge($existingAcl, $aclConfig)]);

        $this->mergeConfigFrom(__DIR__.'/../Admin/Config/system.php', 'core');
    }

    /**
     * Merge the package menu into the admin menu.
     */
    protected function registerAdminMenu(): void
    {
        $menuConfig = require __DIR__.'/../Admin/Config/menu.php';

        $existingMenu = (array) config('menu.admin', []);
        config(['menu.admin' => array_merge($existingMenu, $menuConfig)]);
    }

    /**
     * Load the admin panel routes, views and menu, and register the admin API guard and rate limiter.
     */
    protected function bootAdminPanel(): void
    {
        Route::middleware([
            'web',
            PreventRequestsDuringMaintenance::class,
        ])->group(__DIR__.'/../Admin/Routes/admin.php');

        $this->loadViewsFrom(__DIR__.'/../Admin/Resources/views', 'bagistoapi');

        $this->registerAdminMenu();

        Event::listen('bagisto.admin.customers.customers.view.card.notes.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('bagistoapi::customer-api-sessions.customer-card');
        });

        Auth::extend('admin-api', function ($app, $name, array $config) {
            return new AdminApiGuard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(AdminApiAuthenticator::class),
            );
        });

        Auth::extend(CurrentCustomer::GUARD, function ($app, $name, array $config) {
            return new CustomerApiGuard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(CustomerApiAuthenticator::class),
            );
        });

        RateLimiter::for(ThrottleAdminApi::LIMITER, function (Request $request) {
            return ThrottleAdminApi::limitFor($request, $this->app->make(AdminApiAuthSettings::class));
        });
    }

    /**
     * Merge the given headers into the CORS exposed headers configuration.
     */
    private function ensureCorsExposedHeaders(array $headers): void
    {
        $existing = config('cors.exposed_headers', []);
        $merged = array_values(array_unique(array_merge($existing, $headers)));

        if ($merged !== $existing) {
            config(['cors.exposed_headers' => $merged]);
        }
    }
}
