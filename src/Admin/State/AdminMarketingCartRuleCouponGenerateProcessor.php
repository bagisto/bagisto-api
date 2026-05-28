<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleCouponGenerate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Models\CartRuleCoupon;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;

/**
 * Bulk-generate cart-rule coupons. Mirrors the monolith
 * CartRuleCouponController::store + CartRuleCouponRepository::generateCoupons.
 *
 * Format mapping (the underlying repository charset is keyed by Bagisto's
 * historical spellings — we accept both API-style and core-style names):
 *
 *   API name        → repository key
 *   --------------- ---------------
 *   alphabetic      alphabetical
 *   alphabetical    alphabetical   (core's own spelling, kept for compatibility)
 *   alphanumeric    alphanumeric
 *   numeric         numeric
 */
class AdminMarketingCartRuleCouponGenerateProcessor implements ProcessorInterface
{
    /** API-accepted format values mapped to the repository charset key. */
    protected const FORMAT_MAP = [
        'alphabetic'   => 'alphabetical',
        'alphabetical' => 'alphabetical',
        'alphanumeric' => 'alphanumeric',
        'numeric'      => 'numeric',
    ];

    public function __construct(
        protected CartRuleCouponRepository $couponRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin);

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        $cartRuleId = (int) (
            $uriVariables['cartRuleId']
            ?? request()->route('cartRuleId')
            ?? ($context['args']['input']['cartRuleId'] ?? null)
            ?? ($context['args']['input']['cart_rule_id'] ?? null)
            ?? 0
        );

        if ($isGraphQL) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            if ($cartRuleId <= 0 && isset($rawArgs['cartRuleId'])) {
                $cartRuleId = (int) $rawArgs['cartRuleId'];
            }
            $input = $this->normalizeKeys($rawArgs);
        } else {
            $input = request()->all();
        }

        $dto = $this->handleGenerate($cartRuleId, $input);

        if ($isGraphQL) {
            return $dto;
        }

        return $this->toRestResponse($dto, $operation instanceof Post ? 201 : 200);
    }

    protected function handleGenerate(int $cartRuleId, array $input): AdminMarketingCartRuleCouponGenerate
    {
        $cartRule = CartRule::find($cartRuleId);
        if (! $cartRule) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.cart-rule-not-found'));
        }

        $length = $input['length'] ?? $input['code_length'] ?? null;
        $format = $input['format'] ?? $input['code_format'] ?? null;
        $prefix = $input['prefix'] ?? $input['code_prefix'] ?? '';
        $suffix = $input['suffix'] ?? $input['code_suffix'] ?? '';
        $couponQty = $input['coupon_qty'] ?? null;

        $payload = [
            'length'     => $length,
            'format'     => is_string($format) ? strtolower($format) : $format,
            'prefix'     => $prefix,
            'suffix'     => $suffix,
            'coupon_qty' => $couponQty,
        ];

        $v = Validator::make($payload, [
            'length'     => ['required', 'integer', 'min:4', 'max:30'],
            'format'     => ['required', 'string'],
            'prefix'     => ['nullable', 'string', 'max:50'],
            'suffix'     => ['nullable', 'string', 'max:50'],
            'coupon_qty' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $formatKey = self::FORMAT_MAP[strtolower((string) $format)] ?? null;
        if ($formatKey === null) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.cart-rule-coupon.format-invalid'),
                422,
            );
        }

        $repoPayload = [
            'coupon_qty'  => (int) $couponQty,
            'code_length' => (int) $length,
            'code_format' => $formatKey,
            'code_prefix' => (string) $prefix,
            'code_suffix' => (string) $suffix,
        ];

        $beforeIds = CartRuleCoupon::where('cart_rule_id', $cartRuleId)->pluck('id')->all();

        $this->couponRepository->generateCoupons($repoPayload, $cartRuleId);

        $newCoupons = CartRuleCoupon::where('cart_rule_id', $cartRuleId)
            ->whereNotIn('id', $beforeIds)
            ->orderBy('id', 'asc')
            ->get();

        $dto = new AdminMarketingCartRuleCouponGenerate;
        $dto->id = $cartRuleId;
        $dto->cartRuleId = $cartRuleId;
        $dto->generated = $newCoupons->count();
        $dto->coupons = $newCoupons->map(fn ($c) => [
            'id'         => (int) $c->id,
            'code'       => $c->code,
            'cartRuleId' => (int) $c->cart_rule_id,
            'expiredAt'  => $c->expired_at ? (string) $c->expired_at : null,
        ])->all();
        $dto->success = true;
        $dto->message = __('bagistoapi::app.admin.marketing.cart-rule-coupon.generated', ['count' => $dto->generated]);

        return $dto;
    }

    protected function normalizeKeys(array $args): array
    {
        $map = [
            'cartRuleId' => 'cart_rule_id',
            'couponQty'  => 'coupon_qty',
            'codeLength' => 'code_length',
            'codeFormat' => 'code_format',
            'codePrefix' => 'code_prefix',
            'codeSuffix' => 'code_suffix',
        ];
        $out = [];
        foreach ($args as $k => $v) {
            $out[$map[$k] ?? $k] = $v;
        }

        return $out;
    }

    protected function toRestResponse(AdminMarketingCartRuleCouponGenerate $dto, int $status): JsonResponse
    {
        return new JsonResponse(array_filter([
            'id'         => $dto->id,
            'cartRuleId' => $dto->cartRuleId,
            'generated'  => $dto->generated,
            'coupons'    => $dto->coupons,
            'success'    => $dto->success,
            'message'    => $dto->message,
        ], static fn ($v) => $v !== null), $status);
    }

    protected function assertPermission(object $admin): void
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
        if (! in_array('marketing.promotions.cart_rules.create', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.no-permission'));
        }
    }
}
