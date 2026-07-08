<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\ProductReview;
use Webkul\Product\Repositories\ProductReviewRepository;

class AdminCustomerReviewProcessor implements ProcessorInterface
{
    public const ALLOWED_STATUSES = ['pending', 'approved', 'disapproved'];

    public function __construct(
        protected ProductReviewRepository $reviewRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCustomerReviewUpdateInput) {
            $this->assertPermission($admin, 'customers.reviews.delete');
            $id = (int) basename((string) ($data->id ?? ($context['args']['input']['id'] ?? '')));

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminCustomerReviewUpdateInput
            || ($data instanceof AdminCustomerReview && $operation instanceof Put)) {
            $this->assertPermission($admin, 'customers.reviews.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) ($data->id ?? ($context['args']['input']['id'] ?? ''))));
            $status = $this->resolveStatus($data, $context, $isGraphQL);

            return $this->handleUpdate($id, $status);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'customers.reviews.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleUpdate(int $id, ?string $status): AdminCustomerReview
    {
        $existing = ProductReview::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.review.not-found'));
        }

        if (! $status || ! in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.review.invalid-status'), 422);
        }

        Event::dispatch('customer.review.update.before', $id);

        $this->reviewRepository->update(['status' => $status], $id);

        $fresh = AdminCustomerReview::with(['product', 'customer', 'images'])->find($id);

        Event::dispatch('customer.review.update.after', $fresh);

        return $fresh;
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminCustomerReview
    {
        $existing = ProductReview::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.review.not-found'));
        }

        $snapshot = $asResource
            ? AdminCustomerReview::with(['product', 'customer', 'images'])->find($id)
            : null;

        Event::dispatch('customer.review.delete.before', $id);

        $this->reviewRepository->delete($id);

        Event::dispatch('customer.review.delete.after', $id);

        if ($asResource && $snapshot) {
            $snapshot->actionMessage = __('bagistoapi::app.admin.customer.review.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.customer.review.deleted')];
    }

    protected function resolveStatus(mixed $data, array $context, bool $isGraphQL): ?string
    {
        if ($data instanceof AdminCustomerReviewUpdateInput && $data->status !== null) {
            return $data->status;
        }

        if ($isGraphQL) {
            $args = $context['args']['input'] ?? $context['args'] ?? [];

            return isset($args['status']) ? (string) $args['status'] : null;
        }

        $val = request()->input('status');

        return $val !== null ? (string) $val : null;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.review.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.review.no-permission'));
        }
    }
}
