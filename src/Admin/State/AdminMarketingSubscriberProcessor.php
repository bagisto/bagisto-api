<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSubscriberRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSubscriberUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\SubscribersList;

class AdminMarketingSubscriberProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminMarketingSubscriberItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingSubscriberUpdateInput) {
            $this->assertPermission($admin, 'marketing.communications.subscribers.delete');
            $id = (int) basename((string) ($data->id ?? ($context['args']['input']['id'] ?? '')));

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingSubscriberUpdateInput
            || ($data instanceof AdminMarketingSubscriber && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.communications.subscribers.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) ($data->id ?? ($context['args']['input']['id'] ?? ''))));
            $value = $this->resolveIsSubscribed($data, $context, $isGraphQL);

            return $this->handleUpdate($id, $value, $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.communications.subscribers.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleUpdate(int $id, ?bool $value, bool $isGraphQL = false): AdminMarketingSubscriber|AdminMarketingSubscriberRestDto
    {
        $existing = SubscribersList::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.subscriber.not-found'));
        }

        if ($value === null) {
            throw new InvalidInputException(__('bagistoapi::app.admin.marketing.subscriber.is-subscribed-required'), 422);
        }

        $existing->update(['is_subscribed' => $value ? 1 : 0]);

        if ($existing->customer) {
            $existing->customer->subscribed_to_news_letter = $value ? 1 : 0;
            $existing->customer->save();
        }

        return $this->buildResult($id, $isGraphQL);
    }

    protected function buildResult(int $id, bool $isGraphQL): AdminMarketingSubscriber|AdminMarketingSubscriberRestDto
    {
        if ($isGraphQL) {
            return AdminMarketingSubscriber::with(['channel'])->find($id);
        }

        return $this->itemProvider->buildRestDtoPublic(SubscribersList::with(['customer'])->find($id));
    }

    protected function handleDelete(int $id): array
    {
        $existing = SubscribersList::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.subscriber.not-found'));
        }

        if ($existing->customer) {
            $existing->customer->subscribed_to_news_letter = false;
            $existing->customer->save();
        }

        $existing->delete();

        return ['message' => __('bagistoapi::app.admin.marketing.subscriber.deleted')];
    }

    protected function resolveIsSubscribed(mixed $data, array $context, bool $isGraphQL): ?bool
    {
        if ($data instanceof AdminMarketingSubscriberUpdateInput && $data->is_subscribed !== null) {
            return (bool) $data->is_subscribed;
        }

        if ($isGraphQL) {
            $args = $context['args']['input'] ?? $context['args'] ?? [];
            if (! array_key_exists('is_subscribed', $args) && ! array_key_exists('isSubscribed', $args)) {
                return null;
            }
            $val = $args['is_subscribed'] ?? $args['isSubscribed'];

            return $val === null ? null : (bool) $val;
        }

        $body = request()->all();
        foreach (['is_subscribed', 'isSubscribed'] as $k) {
            if (array_key_exists($k, $body)) {
                $v = $body[$k];
                if ($v === null || $v === '') {
                    return null;
                }
                if (is_string($v)) {
                    $low = strtolower($v);
                    if (in_array($low, ['true', '1', 'yes'], true)) {
                        return true;
                    }
                    if (in_array($low, ['false', '0', 'no'], true)) {
                        return false;
                    }
                }

                return (bool) $v;
            }
        }

        return null;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.subscriber.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.subscriber.no-permission'));
        }
    }
}
