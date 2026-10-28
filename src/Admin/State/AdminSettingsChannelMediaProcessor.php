<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\HandlesAdminMediaUpload;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Helpers\MediaFileName;
use Webkul\Core\Models\Channel;
use Webkul\Core\Repositories\ChannelRepository;

/**
 * Stores and removes the channel logo or favicon the way ChannelRepository::uploadImages does.
 */
class AdminSettingsChannelMediaProcessor implements ProcessorInterface
{
    use ChecksAdminPermission, HandlesAdminMediaUpload;

    /**
     * Upload rules per image, as the channel form validates them.
     */
    protected const MIMES = [
        'logo' => 'mimes:bmp,jpeg,jpg,png,webp',
        'favicon' => 'mimes:bmp,jpeg,jpg,png,webp,ico',
    ];

    public function __construct(
        protected ChannelRepository $channelRepository,
        protected MediaFileName $mediaFileName,
        protected AdminSettingsChannelItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $this->authorizedAdmin('settings.channels.edit', 'bagistoapi::app.admin.settings.channel.no-permission');

        $type = ($operation->getExtraProperties()['media_type'] ?? null) === 'favicon' ? 'favicon' : 'logo';

        $channel = Channel::find((int) $this->pathVariable($uriVariables, 'id'));

        if (! $channel) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.channel.not-found'));
        }

        $file = $operation instanceof Delete
            ? null
            : $this->validatedUpload($type, self::MIMES[$type], [
                'alt_text' => ['nullable', 'string', 'max:255'],
                'file_name' => ['nullable', 'string', 'max:'.MediaFileName::MAX_LENGTH],
            ]);

        Event::dispatch('core.channel.update.before', $channel->id);

        if ($file) {
            $this->store($channel, $type, $file);
        } else {
            $this->channelRepository->uploadImages([], $channel, $type);
        }

        Event::dispatch('core.channel.update.after', $channel);

        return $this->dtoResponse($this->itemProvider->buildRestDtoPublic(
            Channel::with(['locales', 'currencies', 'inventory_sources', 'translations'])->find($channel->id)
        ));
    }

    protected function store(Channel $channel, string $type, UploadedFile $file): void
    {
        if ($channel->{$type}) {
            Storage::delete($channel->{$type});
        }

        $channel->{$type} = $this->mediaFileName->resolve(
            'channel/'.$channel->id,
            request()->input('file_name'),
            $file->getClientOriginalExtension()
        );

        Storage::put($channel->{$type}, $file->get());

        $channel->save();

        if (
            ! request()->has('alt_text')
            || ! in_array($type.'_alt', $channel->translatedAttributes)
        ) {
            return;
        }

        foreach (core()->getRequestedLocaleCodes() as $localeCode) {
            if ($translation = $channel->translate($localeCode)) {
                $translation->{$type.'_alt'} = request()->input('alt_text');
            }
        }

        $channel->save();
    }
}
