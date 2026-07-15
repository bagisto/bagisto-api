<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Admin Catalog Product step-1 create (Phases 5.3 — 5.8 + 5.8-booking).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\ProductController::store
 * minimal-input path:
 *   - Validate type, attribute_family_id, sku (required + unique + Slug rule).
 *   - For configurable: also validate super_attributes (non-empty array of
 *     attribute => option_ids[]). Other types take no extra input at step 1.
 *   - Fire catalog.product.create.before
 *   - $this->productRepository->create([type, attribute_family_id, sku, ...])
 *   - Fire catalog.product.create.after
 *
 * Accepts type ∈ {simple, virtual, downloadable, grouped, bundle, configurable, booking}.
 * For booking, the 5 sub-types (default/appointment/event/rental/table) are
 * distinguished by booking_products.type during the step-2 update (Phase 5.9).
 *
 * Unlike the monolith's two-step UX (which asks for super_attributes in a
 * second request after rendering the family's configurable attributes), this
 * API expects super_attributes inline on the create call — a single-step
 * server flow with an explicit 422 when missing.
 *
 * Permission gate: catalog.products.create (Sanctum-pattern; reads
 * $admin->role->permission_type / ->permissions directly — never bouncer()).
 */
class AdminCatalogProductCreateProcessor implements ProcessorInterface
{
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

        $this->assertPermission($admin, 'catalog.products.create');

        $payload = $this->extractPayload($data, $context);

        $type = $payload['type'] ?? 'simple';
        $sku = $payload['sku'] ?? null;
        $attributeFamilyId = $payload['attribute_family_id'] ?? null;
        $superAttributes = $payload['super_attributes'] ?? null;

        if ($type === null || $type === '') {
            $type = 'simple';
        }

        $supportedTypes = ['simple', 'virtual', 'downloadable', 'grouped', 'bundle', 'configurable', 'booking'];
        if (! in_array($type, $supportedTypes, true)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.create.type-not-yet-supported', ['type' => $type]),
                422,
            );
        }

        if (empty($sku)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.create.sku-required'), 422);
        }

        if (empty($attributeFamilyId)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.create.attribute-family-required'), 422);
        }

        if (! preg_match('/^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/', $sku)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.create.sku-invalid'), 422);
        }

        if (DB::table('products')->where('sku', $sku)->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.create.sku-unique'), 422);
        }

        if (! DB::table('attribute_families')->where('id', (int) $attributeFamilyId)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.create.attribute-family-not-found'),
                422,
            );
        }

        $repoPayload = [
            'type' => $type,
            'attribute_family_id' => (int) $attributeFamilyId,
            'sku' => $sku,
        ];

        if ($type === 'configurable') {
            if ($superAttributes === null) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.create.super-attributes-required'),
                    422,
                );
            }

            if (! is_array($superAttributes) || $superAttributes === []) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.create.super-attributes-invalid'),
                    422,
                );
            }

            $normalizedSuperAttributes = $this->normalizeSuperAttributes($superAttributes);

            if ($normalizedSuperAttributes === []) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.create.super-attributes-invalid'),
                    422,
                );
            }

            $repoPayload['super_attributes'] = $normalizedSuperAttributes;
        }

        try {
            Event::dispatch('catalog.product.create.before');

            $product = $this->productRepository->create($repoPayload);

            Event::dispatch('catalog.product.create.after', $product);
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(
                $e->getMessage() ?: __('bagistoapi::app.admin.product.create.create-failed'),
                500,
            );
        }

        $isGraphQL = ! empty($context['graphql_operation_name']);

        if ($isGraphQL) {
            return $this->detailProvider->loadEloquentForGraphQL((int) $product->id)
                ?? (new AdminCatalogProduct)->forceFill([
                    'id' => (int) $product->id,
                    'sku' => $product->sku,
                    'type' => $product->type,
                    'attribute_family_id' => (int) $product->attribute_family_id,
                ]);
        }

        $reloaded = $this->detailProvider->findEntityPublic((int) $product->id);

        if (! $reloaded) {
            $minimal = new AdminCatalogProductRestDto;
            $minimal->id = (int) $product->id;
            $minimal->sku = $product->sku;
            $minimal->type = $product->type;
            $minimal->attributeFamilyId = (int) $product->attribute_family_id;

            return $minimal;
        }

        return $this->detailProvider->mapToDtoPublic($reloaded);
    }

    /**
     * Read create fields from the GraphQL input args (camelCase) or the REST
     * request body (snake_case or camelCase — we accept both for parity with
     * the rest of the Catalog API surface).
     *
     * @return array{type?:string, sku?:string, attribute_family_id?:int}
     */
    protected function extractPayload(mixed $data, array $context): array
    {
        if ($operation = $context['operation'] ?? null) {
        }

        $args = $context['args']['input'] ?? $context['args'] ?? null;
        if (is_array($args) && $args !== []) {
            return [
                'type' => $args['type'] ?? 'simple',
                'sku' => $args['sku'] ?? null,
                'attribute_family_id' => $args['attributeFamilyId']
                    ?? $args['attribute_family_id']
                    ?? null,
                'super_attributes' => $args['superAttributes']
                    ?? $args['super_attributes']
                    ?? null,
            ];
        }

        $body = request()->all();

        return [
            'type' => $body['type'] ?? 'simple',
            'sku' => $body['sku'] ?? null,
            'attribute_family_id' => $body['attribute_family_id']
                ?? $body['attributeFamilyId']
                ?? null,
            'super_attributes' => $body['super_attributes']
                ?? $body['superAttributes']
                ?? null,
        ];
    }

    /**
     * Normalise super_attributes into the shape Bagisto core's Configurable::create
     * expects: { attribute_code => option_ids[] }.
     *
     * Accepts either:
     *   - { "color": [1, 2], "size": [4, 5] }      (codes — passed through)
     *   - { 23: [1, 2], 24: [4, 5] }              (numeric attribute_ids — looked up)
     *
     * Skips entries whose options aren't a non-empty list of integers, and
     * silently drops unknown attribute_ids. Returns [] if nothing remains —
     * the caller surfaces a 422 super-attributes-invalid in that case.
     *
     * @param  array<int|string, mixed>  $superAttributes
     * @return array<string, int[]>
     */
    protected function normalizeSuperAttributes(array $superAttributes): array
    {
        $result = [];

        foreach ($superAttributes as $key => $options) {
            if (! is_array($options) || $options === []) {
                continue;
            }

            $optionIds = array_values(array_filter(array_map(
                static fn ($v) => is_numeric($v) ? (int) $v : null,
                $options,
            ), static fn ($v) => $v !== null && $v > 0));

            if ($optionIds === []) {
                continue;
            }

            $code = null;
            if (is_string($key) && ! ctype_digit($key)) {
                $code = $key;
            } else {
                $attrId = (int) $key;
                $code = DB::table('attributes')->where('id', $attrId)->value('code');
            }

            if (! $code) {
                continue;
            }

            $result[$code] = $optionIds;
        }

        return $result;
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
