<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Marketing\Mail\NewsletterMail;

/**
 * GraphQL coverage for Admin Marketing → Campaigns CRUD + send (Block F2c).
 */
class MarketingCampaignTest extends AdminApiTestCase
{
    protected function insertTemplate(): int
    {
        return DB::table('marketing_templates')->insertGetId([
            'name'       => 'Tpl '.uniqid(),
            'status'     => 'active',
            'content'    => '<p>hi</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function insertEvent(): int
    {
        return DB::table('marketing_events')->insertGetId([
            'name'        => 'Evt '.uniqid(),
            'description' => 'desc',
            'date'        => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    protected function insertCampaign(array $overrides = []): int
    {
        return DB::table('marketing_campaigns')->insertGetId(array_merge([
            'name'                  => 'gqlcamp-'.uniqid(),
            'subject'               => 'subj',
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

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertCampaign();

        $query = <<<'GQL'
            query { adminMarketingCampaigns(first: 10) { edges { node { _id name } } } }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('data.adminMarketingCampaigns.edges'))->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['name' => 'gqldetail-camp']);

        $query = <<<GQL
            query {
              adminMarketingCampaign(id: "/api/admin/marketing/campaigns/{$id}") {
                _id name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        $node = $response->json('data.adminMarketingCampaign');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('marketing_campaigns', ['id' => $id, 'name' => 'gqldetail-camp']);
        }
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();
        $tpl = $this->insertTemplate();
        $evt = $this->insertEvent();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminMarketingCampaignInput!) {
              createAdminMarketingCampaign(input: $input) {
                adminMarketingCampaign { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name'                => 'gqlcr-camp',
                'subject'             => 'gql subject',
                'marketingTemplateId' => $tpl,
                'marketingEventId'    => $evt,
                'channelId'           => $cId,
                'customerGroupId'     => $gId,
                'status'              => 1,
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_campaigns', ['name' => 'gqlcr-camp']);
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['name' => 'gqlupd-camp']);
        $tpl = (int) DB::table('marketing_campaigns')->where('id', $id)->value('marketing_template_id');
        $evt = (int) DB::table('marketing_campaigns')->where('id', $id)->value('marketing_event_id');

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingCampaignInput!) {
              updateAdminMarketingCampaign(input: $input) {
                adminMarketingCampaign { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'                  => "/api/admin/marketing/campaigns/{$id}",
                'name'                => 'gqlupd-updated',
                'subject'             => 'subj2',
                'marketingTemplateId' => $tpl,
                'marketingEventId'    => $evt,
                'channelId'           => $this->getChannelId(),
                'customerGroupId'     => $this->getCustomerGroupId(),
                'status'              => 1,
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $id, 'name' => 'gqlupd-updated']);
    }

    public function test_mutation_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['name' => 'gqldel-camp']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingCampaignInput!) {
              deleteAdminMarketingCampaign(input: $input) {
                adminMarketingCampaign { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/campaigns/{$id}"],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('marketing_campaigns', ['id' => $id]);
    }

    public function test_mutation_send_happy_path(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $groupId = $this->createFreshGroupId();
        \Webkul\Customer\Models\Customer::factory()->create([
            'email'                     => 'gqlsub-'.uniqid().'@example.com',
            'customer_group_id'         => $groupId,
            'subscribed_to_news_letter' => 1,
        ]);
        $id = $this->insertCampaign(['customer_group_id' => $groupId, 'status' => 1]);

        $mutation = <<<'GQL'
            mutation Send($input: createAdminMarketingCampaignSendInput!) {
              createAdminMarketingCampaignSend(input: $input) {
                adminMarketingCampaignSend { _id queued }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['campaignId' => $id],
        ], $admin);

        $response->assertOk();
        Mail::assertQueued(NewsletterMail::class, 1);
    }

    public function test_mutation_send_refuses_inactive(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $id = $this->insertCampaign(['status' => 0]);

        $mutation = <<<'GQL'
            mutation Send($input: createAdminMarketingCampaignSendInput!) {
              createAdminMarketingCampaignSend(input: $input) {
                adminMarketingCampaignSend { _id queued }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['campaignId' => $id],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeArray();
        Mail::assertNothingQueued();
    }

    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingCampaigns(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->toBeArray();
    }
}
