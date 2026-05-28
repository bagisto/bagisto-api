<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingEventCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingEventUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingEvent;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\Event as EventModel;
use Webkul\Marketing\Repositories\EventRepository;

/**
 * Handles POST, PUT, DELETE on AdminMarketingEvent.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\EventController:
 *   store / update / destroy. Events fired:
 *     marketing.events.create.before / after
 *     marketing.events.update.before / after
 *     marketing.events.delete.before / after
 *
 * Permission resolution: Sanctum pattern — read role->permission_type /
 * role->permissions directly. Never calls bouncer().
 *
 * Validation mirrors EventController:
 *   - name        required
 *   - description required
 *   - date        required|date
 */
class AdminMarketingEventProcessor implements ProcessorInterface
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected AdminMarketingEventItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingEventUpdateInput) {
            $this->assertPermission($admin, 'marketing.communications.events.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingEventCreateInput
            || ($data instanceof AdminMarketingEvent && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.communications.events.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminMarketingEventUpdateInput
            || ($data instanceof AdminMarketingEvent && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.communications.events.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.communications.events.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminMarketingEvent
    {
        $this->validatePayload($input);

        Event::dispatch('marketing.events.create.before');

        $event = $this->eventRepository->create([
            'name'        => $input['name'],
            'description' => $input['description'],
            'date'        => $input['date'],
        ]);

        $event = EventModel::find($event->id);

        Event::dispatch('marketing.events.create.after', $event);

        return $this->itemProvider->mapToDtoPublic($event);
    }

    protected function handleUpdate(int $id, array $input): AdminMarketingEvent
    {
        $event = EventModel::find($id);
        if (! $event) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.event.not-found'));
        }

        $this->validatePayload($input);

        Event::dispatch('marketing.events.update.before', $id);

        $event = $this->eventRepository->update([
            'name'        => $input['name'],
            'description' => $input['description'],
            'date'        => $input['date'],
        ], $id);

        $event = EventModel::find($id);

        Event::dispatch('marketing.events.update.after', $event);

        return $this->itemProvider->mapToDtoPublic($event);
    }

    protected function handleDelete(int $id): array
    {
        $event = EventModel::find($id);
        if (! $event) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.event.not-found'));
        }

        Event::dispatch('marketing.events.delete.before', $id);

        try {
            $this->eventRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.event.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.events.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.event.deleted')];
    }

    protected function validatePayload(array $input): void
    {
        $rules = [
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'date'        => ['required', 'date'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.event.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.event.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingEventCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingEventUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingEventUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function normaliseArgs(array $input): array
    {
        unset($input['id']);

        return $input;
    }

    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result[$key] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
