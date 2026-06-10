<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleCoupon;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Models\CartRuleCoupon;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;

/**
 * Create + Delete a single cart-rule coupon. Mirrors
 * Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleCouponController.
 *
 * Ownership: every coupon touched here must belong to the cart_rule named in
 * the URL. Cross-rule access → 404 (no info leak about whether the coupon
 * exists under another rule).
 */
class AdminMarketingCartRuleCouponProcessor implements ProcessorInterface
{
    public function __construct(
        protected CartRuleCouponRepository $couponRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete') {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.delete');

            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            $cartRuleId = (int) ($rawArgs['cartRuleId'] ?? $rawArgs['cart_rule_id'] ?? 0);
            $couponId = 0;
            if (! empty($rawArgs['id'])) {
                $couponId = (int) basename((string) $rawArgs['id']);
            } elseif ($data instanceof AdminMarketingCartRuleCouponDeleteInput && ! empty($data->id)) {
                $couponId = (int) basename((string) $data->id);
            }

            if ($cartRuleId <= 0 && $couponId > 0) {
                $cartRuleId = (int) (CartRuleCoupon::where('id', $couponId)->value('cart_rule_id') ?? 0);
            }

            return $this->handleDelete($cartRuleId, $couponId);
        }

        if ($isGraphQL && $data instanceof AdminMarketingCartRuleCouponCreateInput) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.create');

            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            $cartRuleId = (int) ($rawArgs['cartRuleId'] ?? $rawArgs['cart_rule_id'] ?? $data->cartRuleId ?? 0);
            $input = $this->normalizeKeys($rawArgs);

            return $this->handleCreate($cartRuleId, $input);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.delete');
            $cartRuleId = (int) ($uriVariables['cartRuleId'] ?? request()->route('cartRuleId') ?? 0);
            $couponId = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

            $dto = $this->handleDelete($cartRuleId, $couponId);

            return $this->toRestResponse($dto, 200);
        }

        if ($operation instanceof Post) {
            $this->assertPermission($admin, 'marketing.promotions.cart_rules.create');
            $cartRuleId = (int) ($uriVariables['cartRuleId'] ?? request()->route('cartRuleId') ?? 0);
            $input = request()->all();

            $dto = $this->handleCreate($cartRuleId, $input);

            return $this->toRestResponse($dto, 201);
        }

        return null;
    }

    protected function handleCreate(int $cartRuleId, array $input): AdminMarketingCartRuleCoupon
    {
        $cartRule = CartRule::find($cartRuleId);
        if (! $cartRule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.cart-rule-not-found'));
        }

        $v = Validator::make($input, [
            'code'               => ['required', 'string', 'max:255', 'unique:cart_rule_coupons,code'],
            'usage_limit'        => ['nullable', 'integer', 'min:0'],
            'usage_per_customer' => ['nullable', 'integer', 'min:0'],
            'expired_at'         => ['nullable', 'date'],
        ]);
        if ($v->fails()) {
            $msg = $v->errors()->first();
            $key = (string) $v->errors()->keys()[0] ?? '';
            $code = $key === 'code' && str_contains(strtolower($msg), 'taken')
                ? __('bagistoapi::app.admin.marketing.cart-rule-coupon.code-taken')
                : $msg;
            throw new InvalidInputException($code, 422);
        }

        Event::dispatch('cart_rules.coupons.create.before');

        $coupon = $this->couponRepository->create([
            'cart_rule_id'       => $cartRuleId,
            'code'               => $input['code'],
            'usage_limit'        => isset($input['usage_limit']) ? (int) $input['usage_limit'] : ($cartRule->uses_per_coupon ?? 0),
            'usage_per_customer' => isset($input['usage_per_customer']) ? (int) $input['usage_per_customer'] : ($cartRule->usage_per_customer ?? 0),
            'is_primary'         => 0,
            'type'               => 1,
            'expired_at'         => $input['expired_at'] ?? ($cartRule->ends_till ?: null),
        ]);

        Event::dispatch('cart_rules.coupons.create.after', $coupon);

        $dto = self::toDto($coupon->fresh());
        $dto->success = true;
        $dto->message = __('bagistoapi::app.admin.marketing.cart-rule-coupon.created');

        return $dto;
    }

    protected function handleDelete(int $cartRuleId, int $couponId): AdminMarketingCartRuleCoupon
    {
        if ($cartRuleId <= 0 || ! CartRule::find($cartRuleId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.cart-rule-not-found'));
        }

        $coupon = CartRuleCoupon::where('id', $couponId)
            ->where('cart_rule_id', $cartRuleId)
            ->first();
        if (! $coupon) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.not-found'));
        }

        Event::dispatch('cart_rules.coupons.delete.before', $coupon);
        $this->couponRepository->delete($couponId);
        Event::dispatch('cart_rules.coupons.delete.after', $coupon);

        $dto = new AdminMarketingCartRuleCoupon;
        $dto->id = $couponId;
        $dto->cartRuleId = $cartRuleId;
        $dto->success = true;
        $dto->message = __('bagistoapi::app.admin.marketing.cart-rule-coupon.deleted');

        return $dto;
    }

    public static function toDto(CartRuleCoupon $coupon): AdminMarketingCartRuleCoupon
    {
        $dto = new AdminMarketingCartRuleCoupon;
        $dto->id = (int) $coupon->id;
        $dto->cartRuleId = (int) $coupon->cart_rule_id;
        $dto->code = $coupon->code;
        $dto->usageLimit = $coupon->usage_limit !== null ? (int) $coupon->usage_limit : null;
        $dto->usagePerCustomer = $coupon->usage_per_customer !== null ? (int) $coupon->usage_per_customer : null;
        $dto->timesUsed = $coupon->times_used !== null ? (int) $coupon->times_used : null;
        $dto->type = $coupon->type !== null ? (int) $coupon->type : null;
        $dto->isPrimary = (bool) $coupon->is_primary;
        $dto->expiredAt = $coupon->expired_at ? (string) $coupon->expired_at : null;
        $dto->createdAt = $coupon->created_at?->toIso8601String();
        $dto->updatedAt = $coupon->updated_at?->toIso8601String();

        return $dto;
    }

    protected function normalizeKeys(array $args): array
    {
        $map = [
            'cartRuleId'       => 'cart_rule_id',
            'usageLimit'       => 'usage_limit',
            'usagePerCustomer' => 'usage_per_customer',
            'expiredAt'        => 'expired_at',
        ];
        $out = [];
        foreach ($args as $k => $v) {
            $out[$map[$k] ?? $k] = $v;
        }

        return $out;
    }

    protected function toRestResponse(AdminMarketingCartRuleCoupon $dto, int $status): JsonResponse
    {
        $payload = array_filter(
            [
                'id'               => $dto->id,
                'cartRuleId'       => $dto->cart_rule_id,
                'code'             => $dto->code,
                'usageLimit'       => $dto->usage_limit,
                'usagePerCustomer' => $dto->usage_per_customer,
                'timesUsed'        => $dto->times_used,
                'type'             => $dto->type,
                'isPrimary'        => $dto->is_primary,
                'expiredAt'        => $dto->expired_at,
                'createdAt'        => $dto->created_at,
                'updatedAt'        => $dto->updated_at,
                'success'          => $dto->success,
                'message'          => $dto->message,
            ],
            static fn ($v) => $v !== null,
        );

        return new JsonResponse($payload, $status);
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.no-permission'));
        }
    }
}
