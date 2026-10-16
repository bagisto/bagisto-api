<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\HandlesAdminMediaUpload;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\User\Models\Admin;

/**
 * Stores and clears an admin avatar the way the Settings → Users form does (admins/{id}).
 */
class AdminSettingsUserImageProcessor implements ProcessorInterface
{
    use ChecksAdminPermission, HandlesAdminMediaUpload;

    public function __construct(protected AdminSettingsUserItemProvider $itemProvider) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $this->authorizedAdmin('settings.users.edit', 'bagistoapi::app.admin.settings.user.no-permission');

        $user = Admin::find((int) $this->pathVariable($uriVariables, 'id'));

        if (! $user) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        $file = $operation instanceof Delete
            ? null
            : $this->validatedUpload('image', ['mimes:jpeg,jpg,png,gif', 'max:10000']);

        Event::dispatch('user.admin.update.before', $user->id);

        if ($user->image) {
            Storage::delete($user->image);
        }

        $user->image = $file?->store('admins/'.$user->id);

        $user->save();

        Event::dispatch('user.admin.update.after', $user);

        return $this->detailResponse($this->itemProvider, Admin::with('role')->find($user->id));
    }
}
