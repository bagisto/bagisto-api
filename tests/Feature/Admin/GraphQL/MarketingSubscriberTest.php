<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\SubscribersList;

/**
 * GraphQL coverage for Admin Marketing → Newsletter Subscribers (Block F2d).
 */
class MarketingSubscriberTest extends AdminApiTestCase
{
    protected function seedSubscriber(array $overrides = []): SubscribersList
    {
        $this->seedRequiredData();

        return SubscribersList::factory()->create(array_merge([
            'is_subscribed' => 1,
        ], $overrides));
    }

    public function test_listing(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $query = <<<'GQL'
            query {
              adminMarketingSubscribers(first: 50) {
                edges { node { id _id email isSubscribed } }
                totalCount
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminMarketingSubscribers.edges') ?? []);
        expect($ids)->toContain($s->id);
    }

    public function test_listing_filter_by_is_subscribed(): void
    {
        $admin = $this->createAdmin();
        $on = $this->seedSubscriber(['is_subscribed' => 1]);
        $off = $this->seedSubscriber(['is_subscribed' => 0]);

        $query = <<<'GQL'
            query($v: Int) {
              adminMarketingSubscribers(first: 50, is_subscribed: $v) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['v' => 0], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminMarketingSubscribers.edges') ?? []);
        expect($ids)->toContain($off->id);
        expect($ids)->not()->toContain($on->id);
    }

    public function test_listing_requires_auth(): void
    {
        $query = 'query { adminMarketingSubscribers(first: 5) { edges { node { _id } } } }';
        $resp = $this->adminGraphQL($query);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_detail(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $query = <<<'GQL'
            query($id: ID!) {
              adminMarketingSubscriber(id: $id) {
                id
                _id
                email
                isSubscribed
                customerId
                customerName
                channel { _id code name }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/marketing/subscribers/'.$s->id], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect($resp->json('data.adminMarketingSubscriber._id'))->toBe($s->id);
    }

    public function test_detail_resolves_channel_object(): void
    {
        $admin = $this->createAdmin();
        $channelId = core()->getCurrentChannel()->id;
        $s = $this->seedSubscriber(['channel_id' => $channelId]);

        $query = <<<'GQL'
            query($id: ID!) {
              adminMarketingSubscriber(id: $id) {
                _id
                channel { _id code name }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/marketing/subscribers/'.$s->id], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        expect($resp->json('data.adminMarketingSubscriber.channel._id'))->toBe($channelId);
        expect($resp->json('data.adminMarketingSubscriber.channel.code'))->not()->toBeNull();
    }

    public function test_update_mutation(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber(['is_subscribed' => 1]);

        $mutation = <<<'GQL'
            mutation($input: updateAdminMarketingSubscriberInput!) {
              updateAdminMarketingSubscriber(input: $input) {
                adminMarketingSubscriber { id _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => '/api/admin/marketing/subscribers/'.$s->id,
                'isSubscribed' => false,
            ],
        ], $admin);
        $resp->assertOk();
        expect((int) SubscribersList::find($s->id)->is_subscribed)->toBe(0);
    }

    public function test_delete_mutation(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminMarketingSubscriberInput!) {
              deleteAdminMarketingSubscriber(input: $input) {
                adminMarketingSubscriber { id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/marketing/subscribers/'.$s->id],
        ], $admin);
        $resp->assertOk();
        expect(SubscribersList::find($s->id))->toBeNull();
    }
}
