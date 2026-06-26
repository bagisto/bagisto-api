<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Marketing\Mail\NewsletterMail;

/**
 * REST coverage for Admin Marketing → Campaigns CRUD + send (Block F2c).
 */
class MarketingCampaignTest extends AdminApiTestCase
{
    protected function insertTemplate(array $overrides = []): int
    {
        return DB::table('marketing_templates')->insertGetId(array_merge([
            'name'       => 'Tpl '.uniqid(),
            'status'     => 'active',
            'content'    => '<p>Hello {{name}}</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function insertEvent(array $overrides = []): int
    {
        return DB::table('marketing_events')->insertGetId(array_merge([
            'name'        => 'Evt '.uniqid(),
            'description' => 'desc',
            'date'        => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
    }

    protected function insertCampaign(array $overrides = []): int
    {
        return DB::table('marketing_campaigns')->insertGetId(array_merge([
            'name'                  => 'Camp '.uniqid(),
            'subject'               => 'Subj',
            'status'                => 1,
            'type'                  => 'email',
            'mail_to'               => '',
            'channel_id'            => $this->getChannelId(),
            'customer_group_id'     => $this->getCustomerGroupId(),
            'marketing_template_id' => $this->insertTemplate(),
            'marketing_event_id'    => $this->insertEvent(),
            'created_at'            => now(),
            'updated_at'            => now(),
        ], $overrides));
    }

    protected function getChannelId(): int
    {
        return (int) DB::table('channels')->first()->id;
    }

    protected function getCustomerGroupId(): int
    {
        return (int) DB::table('customer_groups')->where('code', '!=', 'guest')->first()->id;
    }

    protected function getGuestGroupId(): int
    {
        return (int) DB::table('customer_groups')->where('code', 'guest')->first()->id;
    }

    protected function createFreshGroupId(): int
    {
        return (int) DB::table('customer_groups')->insertGetId([
            'code'            => 'e2e-cg-'.uniqid(),
            'name'            => 'E2E Group '.uniqid(),
            'is_user_defined' => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'Limited '.uniqid(),
            'description'     => 'no campaign perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'API Camp '.uniqid(),
            'subject'               => 'Hello there',
            'marketing_template_id' => $this->insertTemplate(),
            'marketing_event_id'    => $this->insertEvent(),
            'channel_id'            => $this->getChannelId(),
            'customer_group_id'     => $this->getCustomerGroupId(),
            'status'                => 1,
        ], $overrides);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/campaigns')->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertCampaign();

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertCampaign();

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns?per_page=1');
        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'subject', 'status', 'channel', 'customerGroup', 'marketingTemplate', 'marketingEvent']);
        // The four to-one objects are detail-only — null on listing rows.
        expect($row['channel'])->toBeNull();
        expect($row['marketingTemplate'])->toBeNull();
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['name' => 'UniqueCampaignName-X']);
        $this->insertCampaign(['name' => 'Other Campaign']);

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns?name=UniqueCampaignName');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $on = $this->insertCampaign(['status' => 1, 'name' => 'on-camp']);
        $off = $this->insertCampaign(['status' => 0, 'name' => 'off-camp']);

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns?status=0');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off);
        expect($ids)->not->toContain($on);
    }

    public function test_filter_by_channel_and_customer_group(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign();
        $row = DB::table('marketing_campaigns')->where('id', $id)->first();

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns?channel_id='.$row->channel_id.'&customer_group_id='.$row->customer_group_id);
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_sort_by_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertCampaign(['name' => 'zzz-srt-camp']);
        $this->insertCampaign(['name' => 'aaa-srt-camp']);

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns?sort=name&order=asc');
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $idxA = array_search('aaa-srt-camp', $names, true);
        $idxZ = array_search('zzz-srt-camp', $names, true);
        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns?per_page=999');
        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_payload_with_resolved_relations(): void
    {
        $admin = $this->createAdmin();
        $tplId = $this->insertTemplate(['name' => 'My Tpl']);
        $evId = $this->insertEvent(['name' => 'My Evt']);
        $id = $this->insertCampaign([
            'name'                  => 'Detail Camp',
            'marketing_template_id' => $tplId,
            'marketing_event_id'    => $evId,
        ]);

        $response = $this->adminGet($admin, '/api/admin/marketing/campaigns/'.$id);
        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('Detail Camp');
        expect($response->json('marketingTemplate.id'))->toBe($tplId);
        expect($response->json('marketingTemplate.name'))->toBe('My Tpl');
        expect($response->json('marketingEvent.id'))->toBe($evId);
        expect($response->json('marketingEvent.name'))->toBe('My Evt');
        expect($response->json('channel.id'))->toBe($this->getChannelId());
        expect($response->json('customerGroup.id'))->toBe($this->getCustomerGroupId());
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/marketing/campaigns/999999')->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCampaign();
        $this->publicGet('/api/admin/marketing/campaigns/'.$id)->assertStatus(401);
    }

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload(['name' => 'Created Camp']);

        $response = $this->adminPost($admin, '/api/admin/marketing/campaigns', $payload);
        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $id, 'name' => 'Created Camp']);
        // Response carries the four to-one objects, not the old FK scalars.
        expect($response->json('channel.id'))->toBe($payload['channel_id']);
        expect($response->json('customerGroup.id'))->toBe($payload['customer_group_id']);
        expect($response->json('marketingTemplate.id'))->toBe($payload['marketing_template_id']);
        expect($response->json('marketingEvent.id'))->toBe($payload['marketing_event_id']);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['name']);
        $this->adminPost($admin, '/api/admin/marketing/campaigns', $payload)->assertStatus(422);
    }

    public function test_create_missing_subject_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['subject']);
        $this->adminPost($admin, '/api/admin/marketing/campaigns', $payload)->assertStatus(422);
    }

    public function test_create_invalid_template_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/campaigns', $this->basePayload([
            'marketing_template_id' => 999999,
        ]))->assertStatus(422);
    }

    public function test_create_invalid_channel_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/campaigns', $this->basePayload([
            'channel_id' => 999999,
        ]))->assertStatus(422);
    }

    public function test_create_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $this->publicPost('/api/admin/marketing/campaigns', $this->basePayload())->assertStatus(401);
    }

    public function test_create_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $this->adminPost($admin, '/api/admin/marketing/campaigns', $this->basePayload())->assertStatus(403);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['name' => 'Old Name']);

        $payload = $this->basePayload(['name' => 'New Name']);
        $payload['marketing_template_id'] = (int) DB::table('marketing_campaigns')->where('id', $id)->value('marketing_template_id');
        $payload['marketing_event_id'] = (int) DB::table('marketing_campaigns')->where('id', $id)->value('marketing_event_id');

        $response = $this->adminPut($admin, '/api/admin/marketing/campaigns/'.$id, $payload);
        $response->assertOk();
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $id, 'name' => 'New Name']);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPut($admin, '/api/admin/marketing/campaigns/999999', $this->basePayload())->assertStatus(404);
    }

    public function test_update_requires_permission(): void
    {
        $id = $this->insertCampaign();
        $admin = $this->createAdminWithoutPermissions();
        $this->adminPut($admin, '/api/admin/marketing/campaigns/'.$id, $this->basePayload())->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign();

        $response = $this->adminDelete($admin, '/api/admin/marketing/campaigns/'.$id);
        $response->assertOk();
        $this->assertDatabaseMissing('marketing_campaigns', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminDelete($admin, '/api/admin/marketing/campaigns/999999')->assertStatus(404);
    }

    public function test_delete_requires_permission(): void
    {
        $id = $this->insertCampaign();
        $admin = $this->createAdminWithoutPermissions();
        $this->adminDelete($admin, '/api/admin/marketing/campaigns/'.$id)->assertStatus(403);
    }

    public function test_send_happy_path_queues_mailable(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $groupId = $this->createFreshGroupId();

        Customer::factory()->create([
            'email'                     => 'sub1-'.uniqid().'@example.com',
            'customer_group_id'         => $groupId,
            'subscribed_to_news_letter' => 1,
        ]);
        Customer::factory()->create([
            'email'                     => 'sub2-'.uniqid().'@example.com',
            'customer_group_id'         => $groupId,
            'subscribed_to_news_letter' => 1,
        ]);
        Customer::factory()->create([
            'email'                     => 'nope-'.uniqid().'@example.com',
            'customer_group_id'         => $groupId,
            'subscribed_to_news_letter' => 0,
        ]);

        $id = $this->insertCampaign(['customer_group_id' => $groupId, 'status' => 1]);

        $response = $this->adminPost($admin, '/api/admin/marketing/campaigns/'.$id.'/send');
        $response->assertOk();
        expect($response->json('campaignId'))->toBe($id);
        expect((int) $response->json('queued'))->toBe(2);

        Mail::assertQueued(NewsletterMail::class, 2);
    }

    public function test_send_refuses_inactive_campaign(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['status' => 0]);

        $this->adminPost($admin, '/api/admin/marketing/campaigns/'.$id.'/send')->assertStatus(422);
        Mail::assertNothingQueued();
    }

    public function test_send_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/campaigns/999999/send')->assertStatus(404);
    }

    public function test_send_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCampaign();
        $this->publicPost('/api/admin/marketing/campaigns/'.$id.'/send')->assertStatus(401);
    }

    public function test_send_requires_permission(): void
    {
        $id = $this->insertCampaign();
        $admin = $this->createAdminWithoutPermissions();
        $this->adminPost($admin, '/api/admin/marketing/campaigns/'.$id.'/send')->assertStatus(403);
    }

    public function test_send_with_no_subscribers_returns_zero_queued(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $groupId = $this->createFreshGroupId();
        $id = $this->insertCampaign(['status' => 1, 'customer_group_id' => $groupId]);

        $response = $this->adminPost($admin, '/api/admin/marketing/campaigns/'.$id.'/send');
        $response->assertOk();
        expect((int) $response->json('queued'))->toBe(0);
        Mail::assertNothingQueued();
    }
}
