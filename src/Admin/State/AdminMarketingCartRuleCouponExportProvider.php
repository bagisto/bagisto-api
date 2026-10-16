<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\CartRule\Repositories\CartRuleRepository;

/**
 * Streams one cart rule's coupons datagrid export, mirroring the core grid's columns.
 */
class AdminMarketingCartRuleCouponExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    public function __construct(protected CartRuleRepository $cartRuleRepository) {}

    protected function exportPermission(): string
    {
        return 'marketing.promotions.cart_rules';
    }

    protected function exportNoPermissionLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.cart-rule-coupon.no-permission';
    }

    protected function exportFilename(): string
    {
        return 'cart-rule-coupons';
    }

    protected function exportHeaders(): array
    {
        return [
            trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.id'),
            trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.coupon-code'),
            trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.times-used'),
            trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.created-date'),
            trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.expiration-date'),
        ];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            $row->code,
            $row->times_used,
            $row->created_at,
            $row->expired_at,
        ];
    }

    protected function exportQuery(array $args)
    {
        $cartRuleId = (int) request()->route('cartRuleId');

        if ($cartRuleId <= 0 || ! $this->cartRuleRepository->find($cartRuleId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.cart-rule-coupon.cart-rule-not-found'));
        }

        return DB::table('cart_rule_coupons')
            ->select('id', 'code', 'created_at', 'expired_at', 'times_used')
            ->where('cart_rule_id', $cartRuleId)
            ->orderByDesc('id');
    }
}
