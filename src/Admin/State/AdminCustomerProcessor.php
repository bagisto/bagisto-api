<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Repositories\CustomerRepository;

class AdminCustomerProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminCustomerItemProvider $itemProvider,
        protected CustomerRepository $customerRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCustomerUpdateInput) {
            $this->assertPermission($admin, 'customers.customers.delete');
            $id = (int) basename((string) ($data->id ?? ($context['args']['input']['id'] ?? '')));

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminCustomerCreateInput
            || ($data instanceof AdminCustomer && $operation instanceof Post)) {
            $this->assertPermission($admin, 'customers.customers.create');

            return $this->handleCreate($this->resolveInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($data instanceof AdminCustomerUpdateInput
            || ($data instanceof AdminCustomer && $operation instanceof Put)) {
            $this->assertPermission($admin, 'customers.customers.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) ($data->id ?? ($context['args']['input']['id'] ?? ''))));

            return $this->handleUpdate($id, $this->resolveInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'customers.customers.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input, bool $isGraphQL = false): mixed
    {
        $sendPassword = (bool) ($input['send_password'] ?? true);

        $rules = [
            'first_name'        => ['required', 'string'],
            'last_name'         => ['required', 'string'],
            'email'             => ['required', 'email', 'unique:customers,email'],
            'date_of_birth'     => ['nullable', 'date', 'before:today'],
            'customer_group_id' => ['required', 'integer', 'exists:customer_groups,id'],
        ];

        if (! $sendPassword) {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        Event::dispatch('customer.registration.before');

        $password = $sendPassword
            ? (string) rand(100000, 10000000)
            : (string) $input['password'];

        $data = [
            'first_name'                => $input['first_name'],
            'last_name'                 => $input['last_name'],
            'email'                     => $input['email'],
            'phone'                     => $input['phone'] ?? null,
            'gender'                    => $input['gender'] ?? null,
            'date_of_birth'             => $input['date_of_birth'] ?? null,
            'customer_group_id'         => (int) $input['customer_group_id'],
            'channel_id'                => (int) ($input['channel_id'] ?? core()->getCurrentChannel()->id),
            'status'                    => isset($input['status']) ? (int) $input['status'] : 1,
            'subscribed_to_news_letter' => (int) ($input['subscribed_to_news_letter'] ?? 0),
            'password'                  => bcrypt($password),
            'is_verified'               => 1,
        ];

        Event::dispatch('customer.create.before');

        $customer = $this->customerRepository->create($data);

        if ($sendPassword) {
            try {
                if (class_exists(\Webkul\Admin\Mail\Customer\NewCustomerNotification::class)) {
                    Mail::queue(new \Webkul\Admin\Mail\Customer\NewCustomerNotification($customer, $password));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Event::dispatch('customer.create.after', $customer);
        Event::dispatch('customer.registration.after', $customer);

        return $this->buildResponse((int) $customer->id, $isGraphQL);
    }

    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): mixed
    {
        $existing = \Webkul\Customer\Models\Customer::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $rules = [
            'first_name'    => ['sometimes', 'required', 'string'],
            'last_name'     => ['sometimes', 'required', 'string'],
            'email'         => ['sometimes', 'required', 'email', 'unique:customers,email,'.$id],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $patch = [];
        foreach (['first_name', 'last_name', 'email', 'phone', 'gender', 'date_of_birth', 'customer_group_id', 'status', 'is_suspended', 'subscribed_to_news_letter'] as $f) {
            if (array_key_exists($f, $input) && $input[$f] !== null) {
                $patch[$f] = $input[$f];
            }
        }

        if (! empty($input['password'])) {
            $patch['password'] = bcrypt($input['password']);
        }

        Event::dispatch('customer.update.before', $id);

        $this->customerRepository->update($patch, $id);

        $customer = $existing->fresh();

        Event::dispatch('customer.update.after', $customer);

        return $this->buildResponse($id, $isGraphQL);
    }

    protected function buildResponse(int $id, bool $isGraphQL): mixed
    {
        $customer = AdminCustomer::with('group')->find($id);

        return $this->itemProvider->mapForProcessor($customer, $isGraphQL);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminCustomer
    {
        $existing = \Webkul\Customer\Models\Customer::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        if ($this->customerRepository->haveActiveOrders($existing)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.has-active-orders'), 400);
        }

        $snapshot = $asResource ? AdminCustomer::with('group')->find($id) : null;

        Event::dispatch('customer.delete.before', $existing);

        $this->customerRepository->delete($id);

        Event::dispatch('customer.delete.after', $existing);

        if ($asResource && $snapshot) {
            $snapshot->actionMessage = __('bagistoapi::app.admin.customer.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.customer.deleted')];
    }

    protected function resolveInput(mixed $data, array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normalizeKeys($rawArgs);
        }

        return request()->all();
    }

    protected function normalizeKeys(array $args): array
    {
        $map = [
            'firstName'              => 'first_name',
            'lastName'               => 'last_name',
            'dateOfBirth'            => 'date_of_birth',
            'customerGroupId'        => 'customer_group_id',
            'channelId'              => 'channel_id',
            'subscribedToNewsLetter' => 'subscribed_to_news_letter',
            'sendPassword'           => 'send_password',
            'isSuspended'            => 'is_suspended',
        ];
        $out = [];
        foreach ($args as $k => $v) {
            $out[$map[$k] ?? $k] = $v;
        }

        return $out;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
    }
}
