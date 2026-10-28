<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionUpdateInput;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAppearanceSection;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Repositories\SectionRepository;

/**
 * Create / update / delete for appearance sections.
 */
class AdminAppearanceSectionProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use MapsAppearanceSection;

    public function __construct(
        protected SectionRepository $sectionRepository,
        protected AppearanceScopeGuard $scope,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (
            $operation instanceof Delete
            || ($operation instanceof Mutation && $operation->getName() === 'delete')
        ) {
            return $this->handleDelete($data, $operation, $uriVariables, $context);
        }

        if ($data instanceof AdminAppearanceSectionUpdateInput) {
            return $this->handleUpdate($data, $uriVariables, $context);
        }

        if ($data instanceof AdminAppearanceSectionCreateInput) {
            return $this->handleCreate($data, $uriVariables, $context);
        }

        throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.invalid-payload'), 422);
    }

    protected function handleCreate(AdminAppearanceSectionCreateInput $input, array $uriVariables, array $context): mixed
    {
        $this->authorizedAdmin('appearance.sections.create', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $payload = [
            'name' => $input->name ?? ($args['name'] ?? null),
            'type' => $input->type ?? ($args['type'] ?? null),
        ];

        $this->validatePayload($payload, [
            'name' => 'required',
            'type' => 'required|in:'.implode(',', Section::TYPES),
        ]);

        $code = $this->scope->pathValue($uriVariables, 'code') ?? $input->code ?? ($args['code'] ?? null);

        $theme = $this->scope->themeOrFail($code);

        $channel = $this->scope->channel(array_filter(['channel' => $this->requestedChannelId($input->channel, $args)]));

        $this->guardSingleFooter($payload['type'], $theme['code'], (int) $channel->id);

        Event::dispatch('section.create.before');

        $section = $this->sectionRepository->create($payload + [
            'channel_id' => $channel->id,
            'theme_code' => $theme['code'],
            'sort_order' => count($this->scope->orderedSectionIds($theme['code'], (int) $channel->id)) + 1,
            'status' => 0,
            'draft_status' => true,
        ]);

        Event::dispatch('section.create.after', $section);

        $this->sectionRepository->reorder($this->scope->orderedSectionIds($theme['code'], (int) $channel->id));

        return $this->present(
            $this->scope->sectionOrFail((int) $section->id),
            $context,
            __('bagistoapi::app.admin.appearance.section.created'),
        );
    }

    protected function handleUpdate(AdminAppearanceSectionUpdateInput $input, array $uriVariables, array $context): mixed
    {
        $this->authorizedAdmin('appearance.sections.edit', 'bagistoapi::app.admin.appearance.no-permission');

        $args = $context['args']['input'] ?? [];

        $id = (int) basename((string) ($this->scope->pathValue($uriVariables, 'id') ?? $input->id ?? ''));

        $section = $this->scope->sectionOrFail($id ?: null);

        $payload = [
            'name' => $input->name ?? $section->name,
            'type' => $input->type ?? $section->type,
            'sort_order' => $input->sort_order ?? $section->sort_order,
            'channel_id' => $input->channel_id ?? $section->channel_id,
            'theme_code' => $input->theme_code ?? $section->theme_code,
        ];

        $this->validatePayload($payload, [
            'name' => 'required',
            'sort_order' => 'required|numeric',
            'type' => 'required|in:'.implode(',', Section::TYPES),
            'channel_id' => 'required|in:'.implode(',', core()->getAllChannels()->pluck('id')->toArray()),
            'theme_code' => 'required',
        ]);

        $this->guardSingleFooter($payload['type'], $payload['theme_code'], (int) $payload['channel_id'], $section->id);

        $channel = $this->scope->channel(['channel' => $payload['channel_id']]);

        $requestedLocale = $input->locale
            ?? ($args['locale'] ?? null)
            ?? request()->input('locale');

        $locale = $this->scope->locale($channel, array_filter(['locale' => $requestedLocale]))->code;

        $payload['status'] = ! is_null($input->status)
            ? (bool) $input->status
            : (bool) $section->status;

        $payload['locale'] = $locale;

        $options = $this->requestedOptions($input->options, $args);

        $sectionOptions = $options ?? $this->currentOptions($section, $locale);

        $carriesMedia = in_array($payload['type'], [Section::IMAGE_CAROUSEL, Section::SERVICES_CONTENT], true);

        if (! $carriesMedia) {
            $payload[$locale] = ['options' => $sectionOptions];
        }

        request()->merge($payload);

        Event::dispatch('section.update.before', $section->id);

        $updated = $this->sectionRepository->update($payload, $section->id);

        if ($carriesMedia) {
            $translation = $updated->translateOrNew($locale);

            $translation->options = $sectionOptions;

            $translation->save();

            $updated->refresh();
        }

        Event::dispatch('section.update.after', $updated);

        return $this->present(
            $this->scope->sectionOrFail((int) $updated->id),
            $context,
            __('bagistoapi::app.admin.appearance.section.updated'),
        );
    }

    protected function handleDelete(mixed $data, Operation $operation, array $uriVariables, array $context): mixed
    {
        $this->authorizedAdmin('appearance.sections.delete', 'bagistoapi::app.admin.appearance.no-permission');

        $id = $this->scope->pathValue($uriVariables, 'id')
            ?? $context['args']['input']['id']
            ?? $context['args']['id']
            ?? (is_object($data) ? ($data->id ?? null) : null);

        $section = $this->scope->sectionOrFail($id ? (int) basename((string) $id) : null);

        $snapshot = $operation instanceof Mutation ? $this->toEloquent($section) : null;

        Event::dispatch('section.delete.before', $section->id);

        $this->sectionRepository->delete($section->id);

        Event::dispatch('section.delete.after', $section->id);

        if ($snapshot) {
            $snapshot->actionMessage = __('bagistoapi::app.admin.appearance.section.deleted');

            return $snapshot;
        }

        return null;
    }

    protected function requestedChannelId(?int $fromInput, array $args): ?int
    {
        $channel = $fromInput
            ?? ($args['channel'] ?? null)
            ?? request()->input('channel');

        return is_null($channel) ? null : (int) $channel;
    }

    /**
     * The options a write carried, or null when it carried none.
     *
     * @return array<string, mixed>|null
     */
    protected function requestedOptions(?array $fromInput, array $args): ?array
    {
        $options = $fromInput
            ?? ($args['options'] ?? null)
            ?? request()->input('options');

        return is_array($options) ? $options : null;
    }

    /**
     * The options a section already holds for a locale.
     *
     * @return array<string, mixed>
     */
    protected function currentOptions(Section $section, string $locale): array
    {
        $options = $section->translate($locale)?->options;

        return is_array($options) ? $options : [];
    }

    protected function guardSingleFooter(?string $type, ?string $themeCode, int $channelId, ?int $ignoreId = null): void
    {
        if ($type !== Section::FOOTER_LINKS) {
            return;
        }

        $exists = Section::query()
            ->where('type', Section::FOOTER_LINKS)
            ->where('theme_code', $themeCode)
            ->where('channel_id', $channelId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw new InvalidInputException(__('bagistoapi::app.admin.appearance.section.footer-links-exists'), 422);
        }
    }

    protected function validatePayload(array $payload, array $rules): void
    {
        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    protected function present(Section $section, array $context, string $message): mixed
    {
        if ($this->isGraphQL($context)) {
            $resource = $this->toEloquent($section);

            $resource->actionMessage = $message;

            return $resource;
        }

        $dto = $this->toRestDto($section);

        $dto->message = $message;

        return $dto;
    }
}
