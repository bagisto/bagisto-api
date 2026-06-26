<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRule;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Repositories\CartRuleRepository;

/**
 * Copy a cart rule — mirrors the admin datagrid Copy action
 * (Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleController::copy).
 *
 * Replicates the rule with status forced to inactive and the name prefixed
 * "Copy of ...", copies its channel + customer-group associations, and returns
 * the new rule's full detail (same shape as GET /marketing/cart-rules/{id}) so
 * the client can render the prefilled edit form without a follow-up request.
 *
 * Coupons are NOT copied (a specific coupon code is unique and cannot be
 * duplicated) — matching the monolith.
 */
class AdminMarketingCartRuleCopyProcessor implements ProcessorInterface
{
    public function __construct(
        protected CartRuleRepository $cartRuleRepository,
        protected AdminMarketingCartRuleItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'marketing.promotions.cart_rules.create');

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        $id = (int) ($uriVariables['id'] ?? 0);
        if (! $id && $isGraphQL) {
            $rawId = $context['args']['input']['cartRuleId']
                ?? $context['args']['input']['id']
                ?? $context['args']['cartRuleId']
                ?? $context['args']['id']
                ?? null;
            $id = $rawId !== null ? (int) basename((string) $rawId) : 0;
        }

        if (! $id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.cart-rule.id-required'), 422);
        }

        $cartRule = $this->cartRuleRepository->with(['channels', 'customer_groups'])->find($id);
        if (! $cartRule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule.not-found'));
        }

        $copied = $cartRule->replicate()->fill([
            'status' => 0,
            'name'   => __('bagistoapi::app.admin.marketing.cart-rule.copy-of', ['value' => $cartRule->name]),
        ]);

        $copied->save();

        foreach ($cartRule->channels as $channel) {
            $copied->channels()->save($channel);
        }

        foreach ($cartRule->customer_groups as $group) {
            $copied->customer_groups()->save($group);
        }

        $newId = (int) $copied->id;

        if ($isGraphQL) {
            return AdminMarketingCartRule::with(['channels', 'customer_groups'])->find($newId);
        }

        $reloaded = $this->itemProvider->findEntityPublic($newId);

        return $this->itemProvider->buildRestDtoPublic($reloaded);
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule.no-permission'));
        }
    }
}
