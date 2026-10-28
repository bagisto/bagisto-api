<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGdprUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\GDPR\Models\GDPRDataRequest;
use Webkul\GDPR\Repositories\GDPRDataRequestRepository;

class AdminCustomerGdprProcessor implements ProcessorInterface
{
    /**
     * The allowed statuses constant.
     */
    public const ALLOWED_STATUSES = ['pending', 'processing', 'declined', 'approved', 'revoked'];

    /**
     * Create a new instance.
     */
    public function __construct(
        protected AdminCustomerGdprItemProvider $itemProvider,
        protected GDPRDataRequestRepository $gdprRepository,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if (
            $isGraphQL
            && $operation->getName() === 'delete'
        ) {
            $this->assertPermission($admin, 'customers.gdpr_requests.delete');
            $id = $this->extractId($data, $uriVariables, $context);

            return $this->handleDelete($id, true);
        }

        if (
            $data instanceof AdminCustomerGdprUpdateInput
            || (
                $data instanceof AdminCustomerGdprRequest
                && $operation instanceof Put
            )
        ) {
            $this->assertPermission($admin, 'customers.gdpr_requests.edit');
            $id = $this->extractId($data, $uriVariables, $context);
            $input = $this->resolveInput($data, $context, $isGraphQL);

            return $this->handleUpdate($id, $input);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'customers.gdpr_requests.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    /**
     * Handle update.
     */
    protected function handleUpdate(int $id, array $input): AdminCustomerGdprRequest
    {
        $request = GDPRDataRequest::find($id);
        if (! $request) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.gdpr.not-found'));
        }

        $patch = [];

        if (
            array_key_exists('status', $input)
            && $input['status'] !== null
            && $input['status'] !== ''
        ) {
            $status = (string) $input['status'];
            if (! in_array($status, self::ALLOWED_STATUSES, true)) {
                throw new InvalidInputException(__('bagistoapi::app.admin.customer.gdpr.invalid-status'), 422);
            }
            $patch['status'] = $status;
        }

        if (
            array_key_exists('message', $input)
            && $input['message'] !== null
        ) {
            $patch['message'] = (string) $input['message'];
        }

        if (empty($patch)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.gdpr.no-changes'), 422);
        }

        Event::dispatch('customer.gdpr-request.update.before');

        $this->gdprRepository->update($patch, $id);

        $fresh = $request->fresh(['customer']);

        Event::dispatch('customer.account.gdpr-request.update.after', $fresh);

        return $this->itemProvider->mapToDto($fresh);
    }

    /**
     * Handle delete.
     */
    protected function handleDelete(int $id, bool $asResource = false): array|AdminCustomerGdprRequest
    {
        $request = GDPRDataRequest::with(['customer'])->find($id);
        if (! $request) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.gdpr.not-found'));
        }

        $snapshot = $asResource ? $this->itemProvider->mapToDto($request) : null;

        $request->delete();

        if (
            $asResource
            && $snapshot instanceof AdminCustomerGdprRequest
        ) {
            $snapshot->message = __('bagistoapi::app.admin.customer.gdpr.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.customer.gdpr.deleted')];
    }

    /**
     * Extract id.
     */
    protected function extractId(mixed $data, array $uriVariables, array $context): int
    {
        if (! empty($uriVariables['id'])) {
            return (int) $uriVariables['id'];
        }

        $idRaw = $data->id
            ?? ($context['args']['input']['id'] ?? null)
            ?? ($context['args']['id'] ?? null);

        if ($idRaw === null) {
            return 0;
        }

        return (int) basename((string) $idRaw);
    }

    /**
     * Resolve input.
     */
    protected function resolveInput(mixed $data, array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            $args = $context['args']['input'] ?? $context['args'] ?? [];
            unset($args['id'], $args['clientMutationId']);

            return $args;
        }

        return request()->all();
    }

    /**
     * Assert permission.
     */
    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.gdpr.no-permission'));
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

        if (
            ! in_array($permission, $perms, true)
            && ! in_array('*', $perms, true)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.gdpr.no-permission'));
        }
    }
}
