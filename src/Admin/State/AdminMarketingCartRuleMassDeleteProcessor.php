<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCartRuleMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\CartRule\Models\CartRule;

class AdminMarketingCartRuleMassDeleteProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'marketing.promotions.cart_rules.delete');

        $indices = $this->resolveIndices($data, $context);
        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.cart-rule.mass-delete-indices-required'), 422);
        }

        $deleted = [];
        $skipped = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $row = CartRule::find($id);
            if (! $row) {
                continue;
            }
            try {
                Event::dispatch('promotions.cart_rule.delete.before', $id);
                $row->delete();
                Event::dispatch('promotions.cart_rule.delete.after', $id);
                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                $skipped[] = ['id' => $id, 'reason' => __('bagistoapi::app.admin.marketing.cart-rule.delete-failed')];
            }
        }

        $result = new AdminMarketingCartRuleMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->skipped = $skipped;
        $result->message = __('bagistoapi::app.admin.marketing.cart-rule.mass-delete-success');

        return $result;
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

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminMarketingCartRuleMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }
        $fromArgs = $context['args']['input']['indices'] ?? $context['args']['indices'] ?? null;
        if (is_array($fromArgs)) {
            return $fromArgs;
        }
        $fromBody = request()->input('indices');
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }
}
