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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingTemplateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingTemplateUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingTemplate;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\SanitisesHtmlContent;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Support\CoreCapabilities;
use Webkul\Marketing\Repositories\TemplateRepository;

class AdminMarketingTemplateProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use SanitisesHtmlContent;

    /**
     * The allowed statuses constant.
     */
    protected const ALLOWED_STATUSES = ['active', 'inactive', 'draft'];

    /**
     * Create a new instance.
     */
    public function __construct(
        protected TemplateRepository $templateRepository,
        protected AdminMarketingTemplateItemProvider $itemProvider,
        protected CoreCapabilities $capabilities,
    ) {}

    /**
     * Route the email template request to create, update or delete.
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
            && $data instanceof AdminMarketingTemplateUpdateInput
        ) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        if (
            $data instanceof AdminMarketingTemplateCreateInput
            || (
                $data instanceof AdminMarketingTemplate
                && $operation instanceof Post
            )
        ) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if (
            $data instanceof AdminMarketingTemplateUpdateInput
            || (
                $data instanceof AdminMarketingTemplate
                && $operation instanceof Put
            )
        ) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'marketing.communications.email_templates.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    /**
     * Create a template from the payload, with its content sanitised as core does.
     */
    protected function handleCreate(array $input): AdminMarketingTemplate
    {
        $this->validatePayload($input);

        $input = $this->sanitiseHtmlFields($input, ['content']);

        Event::dispatch('marketing.templates.create.before');

        $template = $this->templateRepository->create($input);

        $template = $this->templateRepository->find($template->id);

        Event::dispatch('marketing.templates.create.after', $template);

        return $this->itemProvider->mapToDtoPublic($template);
    }

    /**
     * Update a template from the payload, with its content sanitised as core does.
     */
    protected function handleUpdate(int $id, array $input): AdminMarketingTemplate
    {
        if (! $this->templateRepository->find($id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.template.not-found'));
        }

        $this->validatePayload($input);

        $input = $this->sanitiseHtmlFields($input, ['content']);

        Event::dispatch('marketing.templates.update.before', $id);

        $this->templateRepository->update($input, $id);

        $template = $this->templateRepository->find($id);

        Event::dispatch('marketing.templates.update.after', $template);

        return $this->itemProvider->mapToDtoPublic($template);
    }

    /**
     * Delete a template that no campaign uses.
     */
    protected function handleDelete(int $id): array
    {
        $template = $this->templateRepository->find($id);

        if (! $template) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.template.not-found'));
        }

        if (
            $this->capabilities->hasCampaignRelations()
            && $template->campaigns()->count()
        ) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.template.campaign-associated'),
                400,
            );
        }

        Event::dispatch('marketing.templates.delete.before', $id);

        try {
            $this->templateRepository->delete($id);
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(
                __('bagistoapi::app.admin.marketing.template.delete-failed'),
                500,
            );
        }

        Event::dispatch('marketing.templates.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.marketing.template.deleted')];
    }

    /**
     * Validate the payload against core's template rules.
     */
    protected function validatePayload(array $input): void
    {
        $rules = [
            'name' => ['required', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_STATUSES)],
            'content' => ['required', 'string'],
        ];

        $value = Validator::make($input, $rules);

        if ($value->fails()) {
            throw new InvalidInputException($value->errors()->first(), 422);
        }
    }

    /**
     * Refuse an admin whose role lacks the given permission.
     */
    protected function assertPermission(object $admin, string $permission): void
    {
        if (! $this->adminHasPermission($admin, $permission)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.marketing.template.no-permission'));
        }
    }

    /**
     * Read the create payload from the GraphQL arguments or the REST body.
     */
    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if (
            $isGraphQL
            && $data instanceof AdminMarketingTemplateCreateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    /**
     * Read the id of the template to update from the input or the GraphQL arguments.
     */
    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if (
            $data instanceof AdminMarketingTemplateUpdateInput
            && $data->id
        ) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    /**
     * Read the update payload from the GraphQL arguments or the REST body.
     */
    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if (
            $isGraphQL
            && $data instanceof AdminMarketingTemplateUpdateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normaliseArgs($this->dtoToArray($data, $rawArgs));
        }

        return $this->normaliseArgs(request()->all());
    }

    /**
     * Keep only the fields a template accepts.
     */
    protected function normaliseArgs(array $input): array
    {
        unset($input['id']);

        return array_intersect_key($input, array_flip(['name', 'status', 'content']));
    }

    /**
     * Merge the raw GraphQL arguments with the DTO's set properties, the arguments winning.
     */
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
            if (
                $value !== null
                && ! array_key_exists($key, $result)
            ) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
