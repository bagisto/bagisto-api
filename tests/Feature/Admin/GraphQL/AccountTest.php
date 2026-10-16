<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class AccountTest extends AdminApiTestCase
{
    private const UPDATE = <<<'GQL'
        mutation ($input: updateAdminProfileInput!) {
          updateAdminProfile(input: $input) {
            adminProfile {
              name
              email
              roleName
              message
            }
          }
        }
    GQL;

    public function test_update_mutation_changes_name(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGraphQL(self::UPDATE, ['input' => [
            'id' => '/api/admin/get',
            'name' => 'GraphQL Admin',
            'currentPassword' => $this->adminPassword,
        ]], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertSame('GraphQL Admin', $response->json('data.updateAdminProfile.adminProfile.name'));
        $this->assertSame('GraphQL Admin', $admin->fresh()->name);
    }

    public function test_update_mutation_rejects_wrong_current_password(): void
    {
        $admin = $this->createAdmin(['name' => 'Original']);

        $response = $this->adminGraphQL(self::UPDATE, ['input' => [
            'id' => '/api/admin/get',
            'name' => 'Changed',
            'currentPassword' => 'wrong-password',
        ]], $admin);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertSame('Original', $admin->fresh()->name);
    }

    public function test_update_mutation_requires_authentication(): void
    {
        $response = $this->adminGraphQL(self::UPDATE, ['input' => [
            'id' => '/api/admin/get',
            'name' => 'x',
            'currentPassword' => 'whatever',
        ]]);

        $this->assertNotEmpty($response->json('errors'));
    }
}
