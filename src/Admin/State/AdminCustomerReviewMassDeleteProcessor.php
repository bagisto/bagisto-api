<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReviewMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Product\Models\ProductReview;
use Webkul\Product\Repositories\ProductReviewRepository;

class AdminCustomerReviewMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(protected ProductReviewRepository $reviewRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $indices = $this->resolveIndices($data, $context);
        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.review.mass-delete-indices-required'), 422);
        }

        $deleted = [];
        $skipped = [];

        foreach ($indices as $idx) {
            $id = (int) $idx;
            $review = ProductReview::find($id);
            if (! $review) {
                continue;
            }

            try {
                Event::dispatch('customer.review.delete.before', $id);
                $this->reviewRepository->delete($id);
                Event::dispatch('customer.review.delete.after', $id);
                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                $skipped[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        $result = new AdminCustomerReviewMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->skipped = $skipped;
        $result->message = __('bagistoapi::app.admin.customer.review.mass-delete-success');

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
        if (! in_array('customers.reviews.delete', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.review.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminCustomerReviewMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices'] ?? $context['args']['indices'] ?? null;
        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $body = request()->input('indices');

        return is_array($body) ? $body : [];
    }
}
