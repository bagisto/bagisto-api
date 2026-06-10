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

    /**
     * Type-structure keys. When any is present the update runs the core's
     * full-form path (replace semantics for that structure) with the rest of
     * the product's state reconstructed so nothing else is wiped. When none is
     * present the update runs the surgical attributes-only path.
     */
    protected const STRUCTURE_KEYS = [
        'variants',
        'bundle_options',
        'links',
        'downloadable_links',
        'downloadable_samples',
        'booking',
        'customizable_options',
    ];

    /** Relations replaced wholesale by the core full-form update. */
    protected const RELATION_KEYS = [
        'channels',
        'categories',
        'up_sells',
        'cross_sells',
        'related_products',
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

        $payload = $this->extractPayload($context, $isGraphQL);

        $warnings = [];
        foreach (self::STRIPPED_SUBRESOURCES as $key => $langKey) {
            if (array_key_exists($key, $payload)) {
                unset($payload[$key]);
                $warnings[] = __('bagistoapi::app.admin.product.update.'.$langKey);
            }
        }

        $locale = core()->getRequestedLocaleCodeInRequestedChannel();
        $channel = core()->getRequestedChannelCode();

        if (isset($payload['translations']) && is_array($payload['translations'])) {
            $block = $payload['translations'][$locale] ?? null;
            if (is_array($block)) {
                $payload = array_merge($payload, $this->normaliseLocalePayload($block));
            }

            $otherLocales = array_values(array_diff(array_keys($payload['translations']), [$locale]));
            if ($otherLocales !== []) {
                $warnings[] = __('bagistoapi::app.admin.product.update.translations-single-locale', [
                    'locales' => implode(', ', $otherLocales),
                ]);
            }

            unset($payload['translations']);
        }

        $this->validate($payload, $id);

        $payload['locale'] = $locale;
        $payload['channel'] = $channel;

        $hasStructure = (bool) array_intersect(array_keys($payload), self::STRUCTURE_KEYS);

        try {
            Event::dispatch('catalog.product.update.before', $id);

            if ($hasStructure) {
                $this->mergeCurrentState($payload, $product, $locale, $channel);

                $updated = $this->productRepository->update($payload, $id);
            } else {
                $attributeCodes = $this->resolveAttributeCodes($payload, $product);

                if ($attributeCodes !== []) {
                    $this->normaliseMultiselectValues($payload, $product, $attributeCodes);

                    $updated = $this->productRepository->update($payload, $id, $attributeCodes);
                } else {
                    $updated = $product;
                }

                $this->syncSentRelations($payload, $id);
            }

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
            'extras'              => null,
            'id'                  => null,
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

    /**
     * Reconstruct the product's current state for every field the core update
     * would otherwise wipe when omitted, so a partial PATCH only changes the
     * fields the caller actually sends.
     *
     * The core treats the payload as a full admin-form submission:
     *   - boolean / multiselect / checkbox attribute values are force-written
     *     (an omitted boolean becomes false, an omitted multiselect becomes '');
     *   - channels default to the store default channel when empty;
     *   - categories / up_sells / cross_sells / related_products are synced to
     *     whatever is in the payload (omitted → emptied);
     *   - customer_group_prices not in the payload are deleted.
     *
     * Inventories are intentionally NOT reconstructed — the core skips them when
     * absent, and they have a dedicated endpoint. Images/videos are not touched
     * by the repository update at all.
     */
    protected function mergeCurrentState(array &$payload, Product $product, string $locale, string $channel): void
    {
        $relationDefaults = [
            'channels'         => fn () => $product->channels->pluck('id')->map(fn ($i) => (int) $i)->all(),
            'categories'       => fn () => $product->categories->pluck('id')->map(fn ($i) => (int) $i)->all(),
            'up_sells'         => fn () => $product->up_sells->pluck('id')->map(fn ($i) => (int) $i)->all(),
            'cross_sells'      => fn () => $product->cross_sells->pluck('id')->map(fn ($i) => (int) $i)->all(),
            'related_products' => fn () => $product->related_products->pluck('id')->map(fn ($i) => (int) $i)->all(),
        ];

        foreach ($relationDefaults as $key => $resolver) {
            if (! array_key_exists($key, $payload)) {
                $payload[$key] = $resolver();
            }
        }

        if (! array_key_exists('customer_group_prices', $payload)) {
            $payload['customer_group_prices'] = $product->customer_group_prices
                ->mapWithKeys(fn ($cgp) => [(int) $cgp->id => [
                    'qty'               => (int) ($cgp->qty ?? 1),
                    'value_type'        => $cgp->value_type,
                    'value'             => $cgp->value,
                    'customer_group_id' => $cgp->customer_group_id,
                ]])->all();
        }

        $family = $product->attribute_family;
        if (! $family) {
            return;
        }

        foreach ($family->custom_attributes as $attribute) {
            $type = $attribute->type;
            $isMulti = in_array($type, ['multiselect', 'checkbox'], true);

            if ($type !== 'boolean' && ! $isMulti) {
                continue;
            }

            if (array_key_exists($attribute->code, $payload)) {
                if ($isMulti && is_string($payload[$attribute->code])) {
                    $payload[$attribute->code] = array_values(array_filter(
                        explode(',', $payload[$attribute->code]),
                        fn ($v) => $v !== ''
                    ));
                }

                continue;
            }

            $current = $product->getCustomAttributeValue($attribute);

            if ($type === 'boolean') {
                $payload[$attribute->code] = empty($current) ? 0 : 1;
            } else {
                $payload[$attribute->code] = ($current === null || $current === '')
                    ? []
                    : array_values(array_filter(explode(',', (string) $current), fn ($v) => $v !== ''));
            }
        }
    }

    /**
     * The family-attribute codes present in the payload. Passed to the core as
     * the surgical-update attribute list so only these values are written and
     * the type structure / relations / inventory are left untouched.
     *
     * @return string[]
     */
    protected function resolveAttributeCodes(array $payload, Product $product): array
    {
        $family = $product->attribute_family;
        if (! $family) {
            return [];
        }

        $familyCodes = $family->custom_attributes->pluck('code')->all();

        return array_values(array_intersect($familyCodes, array_keys($payload)));
    }

    /**
     * Normalise a provided multiselect/checkbox value to the array the core
     * expects (it `implode`s the value, so a comma-string would break).
     */
    protected function normaliseMultiselectValues(array &$payload, Product $product, array $codes): void
    {
        $family = $product->attribute_family;
        if (! $family) {
            return;
        }

        foreach ($family->custom_attributes as $attribute) {
            if (! in_array($attribute->code, $codes, true)) {
                continue;
            }

            if (
                in_array($attribute->type, ['multiselect', 'checkbox'], true)
                && is_string($payload[$attribute->code] ?? null)
            ) {
                $payload[$attribute->code] = array_values(array_filter(
                    explode(',', $payload[$attribute->code]),
                    fn ($v) => $v !== ''
                ));
            }
        }
    }

    /**
     * In the surgical path the core never touches relations, so sync only the
     * relation lists the caller actually sent (replace semantics per relation).
     */
    protected function syncSentRelations(array $payload, int $id): void
    {
        $present = array_intersect(self::RELATION_KEYS, array_keys($payload));
        if ($present === []) {
            return;
        }

        $product = Product::find($id);
        if (! $product) {
            return;
        }

        foreach ($present as $relation) {
            if (is_array($payload[$relation])) {
                $product->{$relation}()->sync($payload[$relation]);
            }
        }
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
