<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Admin Catalog Product update (Phase 5.9).
 *
 * Pass-through:
 *   - Strip sub-resource fields (images / inventories / customer_group_prices)
 *     and record a `_warnings` entry for each stripped key.
 *   - Validate the small set of fields the API enforces (sku unique,
 *     url_key unique, booleans, special_price vs price, special_price dates).
 *   - Fire catalog.product.update.before, repo->update(), fire .after.
 *   - Return the full detail DTO (same shape as GET /catalog/products/{id})
 *     plus the `_warnings` array.
 *
 * Permission gate: catalog.products.edit.
 */
class AdminCatalogProductUpdateProcessor implements ProcessorInterface
{
    /** Sub-resource keys silently stripped from the payload (with a warning). */
    protected const STRIPPED_SUBRESOURCES = [
        'images'                => 'sub-resource-stripped-images',
        'videos'                => 'sub-resource-stripped-images',
        'inventories'           => 'sub-resource-stripped-inventories',
        'customer_group_prices' => 'sub-resource-stripped-customer-group-prices',
    ];

    public function __construct(
        protected ProductRepository $productRepository,
        protected AdminCatalogProductDetailProvider $detailProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.products.edit');

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // Resolve the product id.
        $id = (int) ($uriVariables['id'] ?? 0);
        if (! $id && $isGraphQL) {
            $rawId = $context['args']['input']['id'] ?? $context['args']['id'] ?? null;
            if ($rawId) {
                $id = (int) basename((string) $rawId);
            }
        }
        if (! $id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.update.id-required'), 422);
        }

        $product = Product::find($id);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        // Extract payload.
        $payload = $this->extractPayload($context, $isGraphQL);

        // Strip sub-resource fields.
        $warnings = [];
        foreach (self::STRIPPED_SUBRESOURCES as $key => $langKey) {
            if (array_key_exists($key, $payload)) {
                unset($payload[$key]);
                $warnings[] = __('bagistoapi::app.admin.product.update.'.$langKey);
            }
        }

        // ---- Validation ----
        $this->validate($payload, $id);

        // Translations: monolith form sends locale-keyed translations at the
        // top level (e.g. payload[<locale>][name]). If the caller used the
        // `translations: { en: { name } }` shape, lift it back to top-level.
        if (isset($payload['translations']) && is_array($payload['translations'])) {
            foreach ($payload['translations'] as $localeCode => $localePayload) {
                if (is_array($localePayload)) {
                    $existing = $payload[$localeCode] ?? [];
                    $payload[$localeCode] = array_merge($existing, $this->normaliseLocalePayload($localePayload));
                }
            }
            unset($payload['translations']);
        }

        // Mirror url_key into per-locale translations if absent — matches
        // monolith ProductForm behaviour where url_key is per-locale.
        if (! empty($payload['url_key'])) {
            $primaryLocale = core()->getDefaultLocaleCodeFromDefaultChannel() ?? 'en';
            $payload[$primaryLocale]['url_key'] ??= $payload['url_key'];
        }

        try {
            Event::dispatch('catalog.product.update.before', $id);

            $updated = $this->productRepository->update($payload, $id);

            Event::dispatch('catalog.product.update.after', $updated);
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(
                $e->getMessage() ?: __('bagistoapi::app.admin.product.update.update-failed'),
                500,
            );
        }

        $reloaded = $this->detailProvider->findEntityPublic($id);
        if (! $reloaded) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.update.update-failed'), 500);
        }

        /** @var AdminCatalogProduct $dto */
        $dto = $this->detailProvider->mapToDtoPublic($reloaded);

        if ($warnings !== []) {
            $dto->_warnings = $warnings;
        }

        return $dto;
    }

    /**
     * Extract the raw payload from either REST body or GraphQL input args,
     * then merge in the catch-all `extras` map. Also maps a small set of
     * camelCase aliases used by the typed DTO surface back to the snake_case
     * keys the monolith repo expects.
     */
    protected function extractPayload(array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            $args = $context['args']['input'] ?? $context['args'] ?? [];

            return $this->normaliseCamelToSnake(is_array($args) ? $args : []);
        }

        return request()->all();
    }

    /**
     * Map camelCase DTO keys → snake_case payload keys. Unknown keys are
     * passed through unchanged so any custom-attribute code works.
     */
    protected function normaliseCamelToSnake(array $args): array
    {
        $aliasMap = [
            'urlKey'              => 'url_key',
            'visibleIndividually' => 'visible_individually',
            'guestCheckout'       => 'guest_checkout',
            'specialPrice'        => 'special_price',
            'specialPriceFrom'    => 'special_price_from',
            'specialPriceTo'      => 'special_price_to',
            'taxCategoryId'       => 'tax_category_id',
            'superAttributes'     => 'super_attributes',
            'bundleOptions'       => 'bundle_options',
            'downloadableLinks'   => 'downloadable_links',
            'downloadableSamples' => 'downloadable_samples',
            'extras'              => null, // handled below
            'id'                  => null, // resource IRI — drop
        ];

        $out = [];
        foreach ($args as $key => $value) {
            if (array_key_exists($key, $aliasMap)) {
                $mapped = $aliasMap[$key];
                if ($mapped === null) {
                    continue;
                }
                $out[$mapped] = $value;
            } else {
                $out[$key] = $value;
            }
        }

        if (! empty($args['extras']) && is_array($args['extras'])) {
            foreach ($args['extras'] as $k => $v) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /**
     * Lift camelCase translation keys to snake_case so the monolith
     * attribute-value saver finds them under the right column names.
     */
    protected function normaliseLocalePayload(array $localePayload): array
    {
        $aliasMap = [
            'shortDescription' => 'short_description',
            'urlKey'           => 'url_key',
            'metaTitle'        => 'meta_title',
            'metaDescription'  => 'meta_description',
            'metaKeywords'     => 'meta_keywords',
        ];

        $out = [];
        foreach ($localePayload as $k => $v) {
            $out[$aliasMap[$k] ?? $k] = $v;
        }

        return $out;
    }

    protected function validate(array $payload, int $id): void
    {
        if (array_key_exists('sku', $payload)) {
            $sku = (string) $payload['sku'];
            if ($sku === '') {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.create.sku-required'), 422);
            }

            if (! preg_match('/^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/', $sku)) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.create.sku-invalid'), 422);
            }

            $exists = DB::table('products')->where('sku', $sku)->where('id', '!=', $id)->exists();
            if ($exists) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.create.sku-unique'), 422);
            }
        }

        if (array_key_exists('url_key', $payload)) {
            $urlKey = (string) $payload['url_key'];
            if ($urlKey === '') {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.update.url-key-required'), 422);
            }

            // Simple uniqueness check across product_flat.url_key (the only
            // place this column lives reliably). Excludes the current product.
            $dup = DB::table('product_flat')
                ->where('url_key', $urlKey)
                ->where('product_id', '!=', $id)
                ->exists();
            if ($dup) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.update.url-key-unique'), 422);
            }
        }

        foreach (['status', 'visible_individually', 'guest_checkout', 'new', 'featured'] as $boolField) {
            if (array_key_exists($boolField, $payload) && $payload[$boolField] !== null) {
                $v = $payload[$boolField];
                if (! in_array((int) $v, [0, 1], true) || ! is_numeric($v) && ! is_bool($v)) {
                    throw new InvalidInputException(
                        __('bagistoapi::app.admin.product.update.boolean-field-invalid', ['field' => $boolField]),
                        422,
                    );
                }
            }
        }

        if (array_key_exists('special_price', $payload) && $payload['special_price'] !== null && $payload['special_price'] !== '') {
            if (! is_numeric($payload['special_price'])) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.update.special-price-invalid'), 422);
            }

            $price = $payload['price'] ?? null;
            if ($price !== null && is_numeric($price) && (float) $payload['special_price'] >= (float) $price) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.update.special-price-invalid'), 422);
            }
        }

        if (! empty($payload['special_price_from']) && ! empty($payload['special_price_to'])) {
            $from = strtotime((string) $payload['special_price_from']);
            $to = strtotime((string) $payload['special_price_to']);
            if ($from !== false && $to !== false && $to < $from) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.update.special-price-date-range-invalid'),
                    422,
                );
            }
        }

        if (array_key_exists('categories', $payload) && $payload['categories'] !== null && ! is_array($payload['categories'])) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.update.categories-invalid'), 422);
        }

        if (array_key_exists('channels', $payload) && $payload['channels'] !== null && ! is_array($payload['channels'])) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.update.channels-invalid'), 422);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.no-permission'));
        }
    }
}
