<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReviewMassUpdateStatus;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Product\Repositories\ProductReviewRepository;

class AdminCustomerReviewMassUpdateStatusProcessor implements ProcessorInterface
{
    public const ALLOWED_STATUSES = ['pending', 'approved', 'disapproved'];

    public function __construct(protected ProductReviewRepository $reviewRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $payload = $this->resolvePayload($data, $context);
        $indices = $payload['indices'] ?? [];
        $value = $payload['value'] ?? null;

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.review.mass-update-indices-required'), 422);
        }
        if (! is_string($value) || ! in_array($value, self::ALLOWED_STATUSES, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.review.mass-update-value-invalid'), 422);
        }

        $updated = [];
        foreach ($indices as $idx) {
            $id = (int) $idx;
            try {
                Event::dispatch('customer.review.update.before', $id);
                $review = $this->reviewRepository->update(['status' => $value], $id);
                Event::dispatch('customer.review.update.after', $review);
                $updated[] = $id;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $result = new AdminCustomerReviewMassUpdateStatus;
        $result->id = 1;
        $result->updated = $updated;
        $result->value = $value;
        $result->message = __('bagistoapi::app.admin.customer.review.mass-update-success');

        return $result;
    }

    protected function assertPermission(object $admin): void
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
        if (! in_array('customers.reviews.edit', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.review.no-permission'));
        }
    }

    protected function resolvePayload(mixed $data, array $context): array
    {
        if ($data instanceof AdminCustomerReviewMassUpdateStatusInput) {
            return ['indices' => $data->indices ?? [], 'value' => $data->value];
        }

        $args = $context['args']['input'] ?? $context['args'] ?? null;
        if (is_array($args)) {
            return ['indices' => $args['indices'] ?? [], 'value' => $args['value'] ?? null];
        }

        return [
            'indices' => is_array(request()->input('indices')) ? request()->input('indices') : [],
            'value' => request()->input('value'),
        ];
    }
}
