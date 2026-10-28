<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Audit;

use Webkul\BagistoApi\Admin\Audit\AdminApiAuditRecorder;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\CoreConfig;

class AuditCaptureTest extends AdminApiTestCase
{
    /**
     * Test create records a row with new values and actor.
     */
    public function test_create_records_a_row_with_new_values_and_actor(): void
    {
        $admin = $this->createAdmin();
        $code = $this->freshCode();

        $this->adminPost($admin, '/api/admin/settings/currencies', [
            'code' => $code,
            'name' => 'Audit Test Currency',
        ])->assertStatus(201);

        $row = $this->audits()->where('event', 'created')
            ->where('auditable_type', 'like', '%Currency%')->latest('id')->first();

        expect($row)->not->toBeNull();
        expect($row->user_id)->toBe($admin->id);
        expect($row->admin_name)->toBe($admin->name);
        expect($row->token_id)->not->toBeNull();
        expect($row->history_id)->not->toBeNull();
        expect($row->method)->toBe('POST');
        expect($row->version_id)->toBe(1);
        expect($row->new_values['code'] ?? null)->toBe($code);
    }

    /**
     * Test update records before and after.
     */
    public function test_update_records_before_and_after(): void
    {
        $admin = $this->createAdmin();
        $id = \DB::table('currencies')->insertGetId([
            'code' => 'Y'.strtoupper(substr(uniqid(), -2)),
            'name' => 'Old Name',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->putJson('/api/admin/settings/currencies/'.$id, ['name' => 'New Name'], $this->adminHeaders($admin))
            ->assertOk();

        $row = $this->audits()->where('event', 'updated')
            ->where('auditable_type', 'like', '%Currency%')
            ->where('auditable_id', $id)->latest('id')->first();

        expect($row)->not->toBeNull();
        expect($row->old_values['name'] ?? null)->toBe('Old Name');
        expect($row->new_values['name'] ?? null)->toBe('New Name');
    }

    /**
     * Test delete records old values.
     */
    public function test_delete_records_old_values(): void
    {
        $admin = $this->createAdmin();
        $id = \DB::table('currencies')->insertGetId([
            'code' => 'Z'.strtoupper(substr(uniqid(), -2)),
            'name' => 'Doomed', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->deleteJson('/api/admin/settings/currencies/'.$id, [], $this->adminHeaders($admin));

        $row = $this->audits()->where('event', 'deleted')
            ->where('auditable_type', 'like', '%Currency%')
            ->where('auditable_id', $id)->latest('id')->first();

        expect($row)->not->toBeNull();
        expect($row->old_values['name'] ?? null)->toBe('Doomed');
        expect($row->new_values)->toBeNull();
    }

    /**
     * Test version id increments per record.
     */
    public function test_version_id_increments_per_record(): void
    {
        $admin = $this->createAdmin();
        $id = \DB::table('currencies')->insertGetId([
            'code' => 'V'.strtoupper(substr(uniqid(), -2)),
            'name' => 'V1', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->putJson('/api/admin/settings/currencies/'.$id, ['name' => 'V2'], $this->adminHeaders($admin))->assertOk();
        $this->putJson('/api/admin/settings/currencies/'.$id, ['name' => 'V3'], $this->adminHeaders($admin))->assertOk();

        $versions = $this->audits()->where('auditable_type', 'like', '%Currency%')
            ->where('auditable_id', $id)->orderBy('id')->pluck('version_id')->all();

        expect($versions)->toBe([1, 2]);
    }

    /**
     * Test get requests are not audited.
     */
    public function test_get_requests_are_not_audited(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/settings/currencies')->assertOk();

        expect($this->audits()->count())->toBe(0);
    }

    /**
     * Test missing token is not audited.
     */
    public function test_missing_token_is_not_audited(): void
    {
        $this->postJson('/api/admin/settings/currencies', [
            'code' => 'NON',
            'name' => 'x',
        ]);
        expect($this->audits()->count())->toBe(0);
    }

    /**
     * Test sensitive fields are redacted.
     */
    public function test_sensitive_fields_are_redacted(): void
    {
        $admin = $this->createAdmin();
        $role = \DB::table('roles')->insertGetId([
            'name' => 'R'.uniqid(), 'permission_type' => 'all', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $userId = \DB::table('admins')->insertGetId([
            'name' => 'Target', 'email' => 'target'.uniqid().'@ex.com',
            'password' => bcrypt('oldsecret'), 'role_id' => $role, 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->putJson('/api/admin/settings/users/'.$userId, [
            'name' => 'Target', 'email' => \DB::table('admins')->where('id', $userId)->value('email'),
            'role_id' => $role, 'password' => 'newsecret123',
        ], $this->adminHeaders($admin))->assertOk();

        $row = $this->audits()->where('event', 'updated')
            ->where('auditable_type', 'like', '%Admin%')
            ->where('auditable_id', $userId)->latest('id')->first();

        expect($row)->not->toBeNull();
        if (array_key_exists('password', $row->new_values ?? [])) {
            expect($row->new_values['password'])->toBe('[redacted]');
        }
    }

    /**
     * Test core config value is redacted.
     */
    public function test_core_config_value_is_redacted(): void
    {
        $admin = $this->createAdmin();

        $this->postJson('/api/admin/configuration', [
            'slug' => 'general.content.footer',
            'values' => ['general.content.footer.footer_text' => 'plain-visible-text'],
        ], $this->adminHeaders($admin));

        $rows = $this->audits()->where('auditable_type', 'like', '%CoreConfig%')->get();

        foreach ($rows as $row) {
            expect($row->new_values['value'] ?? '[redacted]')->toBe('[redacted]');
        }

        expect(json_encode($rows->pluck('new_values')))->not->toContain('plain-visible-text');
    }

    /**
     * Test value is redacted for core config and secret named rows.
     */
    public function test_value_is_redacted_for_core_config_and_secret_named_rows(): void
    {
        $recorder = app(AdminApiAuditRecorder::class);
        $redact = new \ReflectionMethod($recorder, 'redact');

        $config = $redact->invoke($recorder, [
            'code' => 'general.visible',
            'value' => 'abc',
        ], CoreConfig::class);
        $secretRow = $redact->invoke($recorder, [
            'code' => 'payment.stripe.api_key',
            'value' => 'abc',
        ], null);
        $plain = $redact->invoke($recorder, [
            'code' => 'general.visible',
            'value' => 'abc',
        ], null);
        $named = $redact->invoke($recorder, [
            'client_secret' => 'abc',
            'title' => 'Shown',
        ], null);

        expect($config['value'])->toBe('[redacted]');
        expect($secretRow['value'])->toBe('[redacted]');
        expect($plain['value'])->toBe('abc');
        expect($named['client_secret'])->toBe('[redacted]');
        expect($named['title'])->toBe('Shown');
    }

    /**
     * Test graphql mutation is audited.
     */
    public function test_graphql_mutation_is_audited(): void
    {
        $admin = $this->createAdmin();
        $code = $this->freshCode();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsCurrencyInput!) {
              createAdminSettingsCurrency(input: $input) {
                adminSettingsCurrency { _id }
              }
            }
        GQL;

        $this->postJson('/api/admin/graphql', [
            'query' => $mutation,

            'variables' => ['input' => [
                'code' => $code,
                'name' => 'GraphQL Audit Currency',
            ]],
        ], $this->adminHeaders($admin))->assertOk();

        $row = $this->audits()->where('event', 'created')
            ->where('auditable_type', 'like', '%Currency%')->latest('id')->first();

        expect($row)->not->toBeNull();
        expect($row->new_values['code'] ?? null)->toBe($code);
        expect($row->token_id)->not->toBeNull();
        expect($row->method)->toBe('POST');
        expect(str_ends_with((string) $row->url, 'graphql'))->toBeTrue();
    }

    /**
     * Test graphql query is not audited.
     */
    public function test_graphql_query_is_not_audited(): void
    {
        $admin = $this->createAdmin();

        $this->postJson('/api/admin/graphql', [
            'query' => 'query { adminSettingsCurrencies(first: 1) { edges { node { _id } } } }',
        ], $this->adminHeaders($admin))->assertOk();

        expect($this->audits()->count())->toBe(0);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        AdminApiAudit::query()->delete();
    }

    /**
     * Audits.
     */
    protected function audits()
    {
        return AdminApiAudit::query();
    }

    /**
     * Fresh code.
     */
    protected function freshCode(): string
    {
        return 'Q'.chr(random_int(65, 90)).chr(random_int(65, 90));
    }
}
