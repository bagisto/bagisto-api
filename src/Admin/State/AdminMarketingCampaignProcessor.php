<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\Campaign;
use Webkul\Marketing\Repositories\CampaignRepository;

class AdminMarketingCampaignProcessor implements ProcessorInterface
{
    public function __construct(
        protected CampaignRepository $campaignRepository,
        protected AdminMarketingCampaignItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminMarketingCampaignUpdateInput) {
            $this->assertPermission($admin, 'marketing.communications.campaigns.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if ($data instanceof AdminMarketingCampaignCreateInput
            || ($data instanceof AdminMarketingCampaign && $operation instanceof Post)) {
            $this->assertPermission($admin, 'marketing.communications.campaigns.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($data instanceof AdminMarketingCampaignUpdateInput
            || ($data instanceof AdminMarketingCampaign && $operation instanceof Put)) {
            $this->assertPermission($admin, 'marketing.communications.campaigns.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL), $isGraphQL);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.communications.campaigns.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input, bool $isGraphQL = false): AdminMarketingCampaign|AdminMarketingCampaignRestDto
    {
        $this->validatePayload($input);

        $input['type'] = $input['type'] ?? 'email';
        $input['mail_to'] = $input['mail_to'] ?? '';

        Event::dispatch('marketing.campaigns.create.before');

        $campaign = $this->campaignRepository->create($input);

        $campaign = Campaign::with(['email_template', 'event', 'channel', 'customer_group'])->find($campaign->id);

        Event::dispatch('marketing.campaigns.create.after', $campaign);

        return $this->buildResult((int) $campaign->id, $isGraphQL);
    }

    protected function handleUpdate(int $id, array $input, bool $isGraphQL = false): AdminMarketingCampaign|AdminMarketingCampaignRestDto
    {
        $campaign = Campaign::find($id);
        if (! $campaign) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.campaign.not-found'));
        }

        $input = array_merge([
            'name' => $campaign->name,
            'subject' => $campaign->subject,
            'marketing_template_id' => $campaign->marketing_template_id,
            'marketing_event_id' => $campaign->marketing_event_id,
            'channel_id' => $campaign->channel_id,
            'customer_group_id' => $campaign->customer_group_id,
            'status' => $campaign->status,
        ], array_filter($input, fn ($v) => $v !== null));

        $this->validatePayload($input);

        if (array_key_exists('status', $input)) {
            $input['status'] = $input['status'] ? 1 : 0;
        }

        Event::dispatch('marketing.campaigns.update.before', $id);

        $this->campaignRepository->update($input, $id);

        $campaign = Campaign::with(['email_template', 'event', 'channel', 'customer_group'])->find($id);

        Event::dispatch('marketing.campaigns.update.after', $campaign);

        return $this->buildResult($id, $isGraphQL);
    }

    protected function buildResult(int $id, bool $isGraphQL): AdminMarketingCampaign|AdminMarketingCampaignRestDto
    {
        if ($isGraphQL) {
            return AdminMarketingCampaign::with(['channel', 'customer_group', 'marketing_template'])->find($id);
        }

        return $this->itemProvider->buildRestDtoPublic(
            Campaign::with(['email_template', 'event', 'channel', 'customer_group'])->find($id)
        );
    }

    protected function handleDelete(int $id): array
    {
        $campaign = Campaign::find($id);
        if (! $campaign) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.campaign.not-found'));
        }

        Event::dispatch('marketing.campaigns.delete.before', $id);

        try {
            $this->campaignRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.campaign.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.campaigns.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.campaign.deleted')];
    }

    protected function validatePayload(array $input): void
    {
        $rules = [
            'name' => ['required', 'string'],
            'subject' => ['required', 'string'],
            'marketing_template_id' => ['required', 'integer', 'exists:marketing_templates,id'],
            'marketing_event_id' => ['nullable', 'integer', 'exists:marketing_events,id'],
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'customer_group_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'status' => ['sometimes', 'nullable', 'in:0,1'],
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.campaign.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.campaign.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingCampaignCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminMarketingCampaignUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminMarketingCampaignUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    /**
     * Map camelCase → snake_case for keys we accept and strip unsupported ones.
     */
    protected function normaliseArgs(array $input): array
    {
        $camelToSnake = [
            'marketingTemplateId' => 'marketing_template_id',
            'marketingEventId' => 'marketing_event_id',
            'channelId' => 'channel_id',
            'customerGroupId' => 'customer_group_id',
        ];

        foreach ($camelToSnake as $camel => $snake) {
            if (array_key_exists($camel, $input) && ! array_key_exists($snake, $input)) {
                $input[$snake] = $input[$camel];
            }
            unset($input[$camel]);
        }

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
