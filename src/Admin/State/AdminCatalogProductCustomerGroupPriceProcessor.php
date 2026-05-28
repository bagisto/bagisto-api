<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation as GraphQlMutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCustomerGroupPriceCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCustomerGroupPriceUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductCustomerGroupPrice;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductCustomerGroupPrice;

/**
 * POST / PUT / DELETE for the admin product customer-group-prices sub-resource.
 *
 * Permission: catalog.products.edit. Fires catalog.product.update.{before,after}
 * around every write so any core listener (cache flush, indexer, etc.) runs.
 *
 * Uniqueness: (qty, customer_group_id) must be unique per product. The DB
 * also enforces a unique_id column ("{qty}|{product_id}|{customer_group_id}")
 * — we surface that as a 422 before insert/update.
 */
class AdminCatalogProductCustomerGroupPriceProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.products.edit');

        $productId = $this->resolveProductId($uriVariables, $context, $data);
        $product = $productId > 0 ? Product::find($productId) : null;
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        // Put must be checked before Post, since both can run on this processor.
        if ($operation instanceof Put) {
            return $this->handleUpdate($product, $this->resolveRowId($uriVariables, $context, $data), $this->resolveInput($data, $context));
        }

        if ($operation instanceof Delete) {
            return $this->handleDelete($product, $this->resolveRowId($uriVariables, $context, $data));
        }

        if ($operation instanceof Post) {
            return $this->handleCreate($product, $this->resolveInput($data, $context));
        }

        if ($operation instanceof GraphQlMutation) {
            $name = $operation->getName();

            if ($name === 'create') {
                return $this->handleCreate($product, $this->resolveInput($data, $context));
            }

            if ($name === 'update') {
                return $this->handleUpdate($product, $this->resolveRowId($uriVariables, $context, $data), $this->resolveInput($data, $context));
            }

            if ($name === 'delete') {
                return $this->handleDelete($product, $this->resolveRowId($uriVariables, $context, $data));
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleCreate(Product $product, array $input): AdminCatalogProductCustomerGroupPrice
    {
        $this->validateInput($input, /* forUpdate */ false);
        $this->assertCustomerGroupExists($input['customer_group_id'] ?? null);
        $this->assertUniqueQtyGroup($product->id, (int) $input['qty'], $input['customer_group_id'] ?? null, null);

        Event::dispatch('catalog.product.update.before', $product->id);

        $row = new ProductCustomerGroupPrice;
        $row->product_id = $product->id;
        $row->qty = (int) $input['qty'];
        $row->value_type = $input['value_type'];
        $row->value = (float) $input['value'];
        $row->customer_group_id = $input['customer_group_id'] ?? null;
        $row->unique_id = $this->computeUniqueId($product->id, (int) $input['qty'], $row->customer_group_id);
        $row->save();

        Event::dispatch('catalog.product.update.after', $product);

        return $this->toDto($row);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleUpdate(Product $product, int $rowId, array $input): AdminCatalogProductCustomerGroupPrice
    {
        $row = ProductCustomerGroupPrice::find($rowId);
        if (! $row || (int) $row->product_id !== (int) $product->id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.customer-group-price.not-found'));
        }

        $this->validateInput($input, /* forUpdate */ true);

        if (array_key_exists('customer_group_id', $input)) {
            $this->assertCustomerGroupExists($input['customer_group_id']);
        }

        $newQty = array_key_exists('qty', $input) ? (int) $input['qty'] : (int) $row->qty;
        $newGroup = array_key_exists('customer_group_id', $input) ? $input['customer_group_id'] : $row->customer_group_id;

        $this->assertUniqueQtyGroup($product->id, $newQty, $newGroup, (int) $row->id);

        Event::dispatch('catalog.product.update.before', $product->id);

        if (array_key_exists('qty', $input)) {
            $row->qty = $newQty;
        }
        if (array_key_exists('value_type', $input)) {
            $row->value_type = $input['value_type'];
        }
        if (array_key_exists('value', $input)) {
            $row->value = (float) $input['value'];
        }
        if (array_key_exists('customer_group_id', $input)) {
            $row->customer_group_id = $newGroup;
        }
        $row->unique_id = $this->computeUniqueId($product->id, (int) $row->qty, $row->customer_group_id);
        $row->save();

        Event::dispatch('catalog.product.update.after', $product);

        return $this->toDto($row->fresh());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleDelete(Product $product, int $rowId): array
    {
        $row = ProductCustomerGroupPrice::find($rowId);
        if (! $row || (int) $row->product_id !== (int) $product->id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.customer-group-price.not-found'));
        }

        Event::dispatch('catalog.product.update.before', $product->id);

        $row->delete();

        Event::dispatch('catalog.product.update.after', $product);

        return ['message' => __('bagistoapi::app.admin.product.customer-group-price.deleted')];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function validateInput(array $input, bool $forUpdate): void
    {
        // For create, qty/value_type/value are required. For update they're optional but
        // if present must still be valid.
        $rules = [
            'qty'        => ($forUpdate ? 'sometimes|' : '').'required|integer|min:1',
            'value_type' => ($forUpdate ? 'sometimes|' : '').'required|in:fixed,discount',
            'value'      => ($forUpdate ? 'sometimes|' : '').'required|numeric|min:0',
        ];

        $messages = [
            'qty.required'        => __('bagistoapi::app.admin.product.customer-group-price.qty-required'),
            'qty.integer'         => __('bagistoapi::app.admin.product.customer-group-price.qty-invalid'),
            'qty.min'             => __('bagistoapi::app.admin.product.customer-group-price.qty-invalid'),
            'value_type.required' => __('bagistoapi::app.admin.product.customer-group-price.value-type-required'),
            'value_type.in'       => __('bagistoapi::app.admin.product.customer-group-price.value-type-invalid'),
            'value.required'      => __('bagistoapi::app.admin.product.customer-group-price.value-required'),
            'value.numeric'       => __('bagistoapi::app.admin.product.customer-group-price.value-invalid'),
            'value.min'           => __('bagistoapi::app.admin.product.customer-group-price.value-invalid'),
        ];

        $v = Validator::make($input, $rules, $messages);

        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function assertCustomerGroupExists(mixed $groupId): void
    {
        if ($groupId === null || $groupId === '') {
            return;
        }

        if (! CustomerGroup::where('id', (int) $groupId)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.customer-group-price.customer-group-not-found'),
                422,
            );
        }
    }

    protected function assertUniqueQtyGroup(int $productId, int $qty, mixed $groupId, ?int $ignoreRowId): void
    {
        $q = ProductCustomerGroupPrice::query()
            ->where('product_id', $productId)
            ->where('qty', $qty);

        if ($groupId === null || $groupId === '') {
            $q->whereNull('customer_group_id');
        } else {
            $q->where('customer_group_id', (int) $groupId);
        }

        if ($ignoreRowId !== null) {
            $q->where('id', '!=', $ignoreRowId);
        }

        if ($q->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.customer-group-price.duplicate-qty-group'),
                422,
            );
        }
    }

    protected function computeUniqueId(int $productId, int $qty, mixed $groupId): string
    {
        return implode('|', array_filter([
            (string) $qty,
            (string) $productId,
            $groupId === null || $groupId === '' ? null : (string) $groupId,
        ]));
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.customer-group-price.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.product.customer-group-price.no-permission'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Resolution helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveProductId(array $uriVariables, array $context, mixed $data): int
    {
        $raw = $uriVariables['productId']
            ?? $context['args']['input']['productId']
            ?? $context['args']['productId']
            ?? null;

        if ($raw === null && (
            $data instanceof AdminCatalogProductCustomerGroupPriceCreateInput
            || $data instanceof AdminCatalogProductCustomerGroupPriceUpdateInput
        )) {
            $raw = $data->productId;
        }

        $raw = $raw ?? request()->route('productId') ?? request()->input('productId') ?? 0;

        return (int) $raw;
    }

    protected function resolveRowId(array $uriVariables, array $context, mixed $data): int
    {
        $raw = $uriVariables['id']
            ?? $context['args']['input']['id']
            ?? $context['args']['id']
            ?? null;

        if ($raw === null && $data instanceof AdminCatalogProductCustomerGroupPriceUpdateInput) {
            $raw = $data->id;
        }

        $raw = $raw ?? request()->route('id') ?? 0;

        // Strip IRI if present
        if (is_string($raw) && ! ctype_digit($raw)) {
            $raw = basename($raw);
        }

        return (int) $raw;
    }

    protected function resolveInput(mixed $data, array $context): array
    {
        // GraphQL args take precedence when present
        $args = $context['args']['input'] ?? null;
        if (is_array($args) && ! empty($args)) {
            // Strip routing keys
            unset($args['productId'], $args['id']);

            // Normalize camelCase customer-group key from GraphQL
            if (array_key_exists('customerGroupId', $args)) {
                $args['customer_group_id'] = $args['customerGroupId'];
                unset($args['customerGroupId']);
            }
            if (array_key_exists('valueType', $args)) {
                $args['value_type'] = $args['valueType'];
                unset($args['valueType']);
            }

            return $args;
        }

        // REST: read raw body so snake_case keys survive
        $body = request()->except(['_method', '_token']);

        // Translate camelCase (in case clients send camel)
        if (array_key_exists('customerGroupId', $body)) {
            $body['customer_group_id'] = $body['customerGroupId'];
            unset($body['customerGroupId']);
        }
        if (array_key_exists('valueType', $body)) {
            $body['value_type'] = $body['valueType'];
            unset($body['valueType']);
        }

        return $body;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DTO mapping
    // ─────────────────────────────────────────────────────────────────────────

    protected function toDto(ProductCustomerGroupPrice $row): AdminCatalogProductCustomerGroupPrice
    {
        $row->loadMissing('customer_group');

        $dto = new AdminCatalogProductCustomerGroupPrice;
        $dto->id = (int) $row->id;
        $dto->productId = (int) $row->product_id;
        $dto->qty = (int) $row->qty;
        $dto->valueType = (string) $row->value_type;
        $dto->value = $row->value !== null ? (float) $row->value : null;
        $dto->customerGroupId = $row->customer_group_id !== null ? (int) $row->customer_group_id : null;
        $dto->customerGroupName = $row->customer_group?->name;

        return $dto;
    }
}
