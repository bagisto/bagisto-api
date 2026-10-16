<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class AclTest extends AdminApiTestCase
{
    protected string $query = <<<'GQL'
        query {
          getAdminAcl {
            tree
          }
        }
    GQL;

    public function test_query_returns_the_acl_tree(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGraphQL($this->query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $tree = $response->json('data.getAdminAcl.tree');
        expect($tree)->toBeArray()->and($tree)->not->toBeEmpty();
        expect($tree[0])->toHaveKeys(['key', 'name', 'route', 'sort', 'children']);
        expect(collect($tree)->pluck('key')->all())->toContain('dashboard');
    }

    public function test_requires_authentication(): void
    {
        $response = $this->adminGraphQL($this->query);

        expect($response->json('data.getAdminAcl'))->toBeNull();
    }

    public function test_requires_the_roles_permission(): void
    {
        $role = Role::create([
            'name' => 'no-roles-gql',
            'description' => 'No roles access',
            'permission_type' => 'custom',
            'permissions' => ['dashboard'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminGraphQL($this->query, [], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }
}
