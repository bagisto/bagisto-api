<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminProfile;
use Webkul\BagistoApi\Admin\State\AdminProfileProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

/**
 * GraphQL resolver for the authenticated admin profile query.
 */
class AdminProfileQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): AdminProfile
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthorizationException(__('bagistoapi::admin.profile.unauthenticated'));
        }

        $data = AdminProfileProvider::toArray($admin);

        $profile = new AdminProfile;
        $profile->id = $data['id'];
        $profile->name = $data['name'];
        $profile->email = $data['email'];
        $profile->image = $data['image'];
        $profile->status = $data['status'];
        $profile->roleId = $data['roleId'];
        $profile->roleName = $data['roleName'];
        $profile->success = true;
        $profile->message = null;

        return $profile;
    }
}
