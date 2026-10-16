<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Models\AdminProfile;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Helpers\MediaFileName;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

/**
 * The admin's own account, mirroring AccountController::update. The form's single submit is
 * split in two: PUT /account for name, email and password, and /account/image for the avatar.
 */
class AdminAccountProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminRepository $adminRepository,
        protected MediaFileName $mediaFileName,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = $this->adminRepository->find(AdminReadPermissions::authorize()->id);

        if ($operation instanceof Post) {
            $admin = $this->uploadImage($admin);
        } elseif ($operation instanceof Delete) {
            $admin = $this->removeImage($admin);
        } else {
            $admin = $this->update($admin, $this->payload($data, $context));
        }

        $profile = AdminProfileProvider::toArray($admin);
        $profile['message'] = __('bagistoapi::app.admin.account.update-success');

        if ($operation instanceof Mutation) {
            return $this->toResource($profile);
        }

        return new JsonResponse($profile, 200);
    }

    /**
     * Update name, email and password with core's validation and current-password check.
     */
    protected function update(Admin $admin, array $input): Admin
    {
        $this->validate($input, [
            'name' => 'required',
            'email' => 'email|unique:admins,email,'.$admin->id,
            'password' => 'nullable|min:6|confirmed',
            'current_password' => 'required|min:6',
        ]);

        $this->assertCurrentPassword($admin, (string) $input['current_password']);

        $data = array_filter([
            'name' => $input['name'],
            'email' => $input['email'],
        ], fn ($value) => $value !== null);

        $isPasswordChanged = ! empty($input['password']);

        if ($isPasswordChanged) {
            $data['password'] = bcrypt($input['password']);
        }

        $this->adminRepository->update($data, $admin->id);

        $admin = $this->adminRepository->find($admin->id);

        if ($isPasswordChanged) {
            Event::dispatch('admin.password.update.after', $admin);
        }

        return $admin;
    }

    /**
     * Store a new avatar the way core does, replacing the previous file.
     */
    protected function uploadImage(Admin $admin): Admin
    {
        $file = request()->file('image');

        $file = is_array($file) ? current($file) : $file;

        $input = [
            'image' => $file,
            'current_password' => request()->input('current_password'),
            'file_name' => request()->input('file_name'),
        ];

        $this->validate($input, [
            'image' => ['required', 'file', 'mimes:bmp,jpeg,jpg,png,webp'],
            'current_password' => 'required|min:6',
            'file_name' => ['nullable', 'string', 'max:'.MediaFileName::MAX_LENGTH],
        ]);

        $this->assertCurrentPassword($admin, (string) $input['current_password']);

        if ($admin->image) {
            Storage::delete($admin->image);
        }

        $path = $this->mediaFileName->resolve('admins/'.$admin->id, $input['file_name'], $file->getClientOriginalExtension());

        Storage::put($path, $file->get());

        $this->adminRepository->update(['image' => $path], $admin->id);

        return $this->adminRepository->find($admin->id);
    }

    /**
     * Delete the avatar file and clear the column.
     */
    protected function removeImage(Admin $admin): Admin
    {
        $input = ['current_password' => request()->input('current_password')];

        $this->validate($input, ['current_password' => 'required|min:6']);

        $this->assertCurrentPassword($admin, (string) $input['current_password']);

        if ($admin->image) {
            Storage::delete($admin->image);
        }

        $this->adminRepository->update(['image' => null], $admin->id);

        return $this->adminRepository->find($admin->id);
    }

    protected function assertCurrentPassword(Admin $admin, string $currentPassword): void
    {
        if (! Hash::check($currentPassword, $admin->password)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.account.invalid-password'), 422);
        }
    }

    protected function validate(array $input, array $rules): void
    {
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    /**
     * Collect the account fields from the DTO, the GraphQL args or the REST body.
     */
    protected function payload(mixed $data, array $context): array
    {
        $args = $context['args']['input'] ?? [];

        $input = [];

        foreach (['name', 'email', 'password', 'password_confirmation', 'current_password'] as $key) {
            $camel = lcfirst(str_replace('_', '', ucwords($key, '_')));

            $value = is_object($data) && property_exists($data, $key) ? $data->{$key} : null;

            $input[$key] = $value ?? $args[$camel] ?? $args[$key] ?? request()->input($key) ?? request()->input($camel);
        }

        return $input;
    }

    protected function toResource(array $profile): AdminProfile
    {
        $resource = new AdminProfile;

        foreach ($profile as $key => $value) {
            $resource->{$key} = $value;
        }

        return $resource;
    }
}
