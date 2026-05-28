<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\SubscribersList;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;

/**
 * REST coverage for Admin Marketing → Newsletter Subscribers (Block F2d).
 */
class MarketingSubscriberTest extends AdminApiTestCase
{
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete($admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function seedSubscriber(array $overrides = []): SubscribersList
    {
        $this->seedRequiredData();

        return SubscribersList::factory()->create(array_merge([
            'is_subscribed' => 1,
        ], $overrides));
    }

    protected function seedCustomer(): Customer
    {
        $this->seedRequiredData();
        $group = CustomerGroup::where('code', 'general')->first();

        return Customer::factory()->create([
            'customer_group_id' => $group->id,
            'status'            => 1,
        ]);
    }

    // ----------------------------------------------------------------- Auth

    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/subscribers')->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $s = $this->seedSubscriber();
        $this->publicGet('/api/admin/marketing/subscribers/'.$s->id)->assertStatus(401);
    }

    public function test_update_requires_auth(): void
    {
        $s = $this->seedSubscriber();
        $this->putJson('/api/admin/marketing/subscribers/'.$s->id, ['is_subscribed' => false])->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $s = $this->seedSubscriber();
        $this->deleteJson('/api/admin/marketing/subscribers/'.$s->id)->assertStatus(401);
    }

    // -------------------------------------------------------------- Listing

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_listing_returns_seeded_subscriber(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $s->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['email', 'channelId', 'isSubscribed']);
    }

    public function test_listing_filter_by_email(): void
    {
        $admin = $this->createAdmin();
        $unique = 'distinctive-'.uniqid().'@example.test';
        $hit = $this->seedSubscriber(['email' => $unique]);
        $miss = $this->seedSubscriber(['email' => 'other-'.uniqid().'@example.test']);

        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers?email=distinctive-&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($hit->id);
        expect($ids)->not()->toContain($miss->id);
    }

    public function test_listing_filter_by_is_subscribed(): void
    {
        $admin = $this->createAdmin();
        $on = $this->seedSubscriber(['is_subscribed' => 1]);
        $off = $this->seedSubscriber(['is_subscribed' => 0]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers?is_subscribed=0&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off->id);
        expect($ids)->not()->toContain($on->id);
    }

    public function test_listing_filter_by_channel(): void
    {
        $admin = $this->createAdmin();
        $channelId = core()->getCurrentChannel()->id;
        $s = $this->seedSubscriber(['channel_id' => $channelId]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers?channel_id='.$channelId.'&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($s->id);
    }

    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_listing_sort_by_email_asc(): void
    {
        $admin = $this->createAdmin();
        $this->seedSubscriber(['email' => 'a-'.uniqid().'@example.test']);
        $this->seedSubscriber(['email' => 'z-'.uniqid().'@example.test']);

        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers?sort=email&order=asc&per_page=50');
        $resp->assertOk();
        $emails = collect($resp->json('data'))->pluck('email')->all();
        $sorted = $emails;
        sort($sorted, SORT_FLAG_CASE | SORT_STRING);
        expect($emails)->toEqual($sorted);
    }

    // ---------------------------------------------------------------- Detail

    public function test_detail_returns_subscriber(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $resp = $this->adminGet($admin, '/api/admin/marketing/subscribers/'.$s->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($s->id);
        expect($resp->json('email'))->toBe($s->email);
        expect($resp->json('isSubscribed'))->toBeTrue();
    }

    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/marketing/subscribers/9999999')->assertStatus(404);
    }

    // -------------------------------------------------------------- Update

    public function test_update_unsubscribe(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber(['is_subscribed' => 1]);

        $resp = $this->adminPut($admin, '/api/admin/marketing/subscribers/'.$s->id, ['is_subscribed' => false]);
        $resp->assertOk();
        expect($resp->json('isSubscribed'))->toBeFalse();
        expect((int) SubscribersList::find($s->id)->is_subscribed)->toBe(0);
    }

    public function test_update_resubscribe(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber(['is_subscribed' => 0]);

        $resp = $this->adminPut($admin, '/api/admin/marketing/subscribers/'.$s->id, ['is_subscribed' => true]);
        $resp->assertOk();
        expect((int) SubscribersList::find($s->id)->is_subscribed)->toBe(1);
    }

    public function test_update_mirrors_flag_to_customer(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();
        $customer->subscribed_to_news_letter = 1;
        $customer->save();
        $s = $this->seedSubscriber([
            'is_subscribed' => 1,
            'customer_id'   => $customer->id,
            'email'         => $customer->email,
        ]);

        $resp = $this->adminPut($admin, '/api/admin/marketing/subscribers/'.$s->id, ['is_subscribed' => false]);
        $resp->assertOk();
        expect((int) Customer::find($customer->id)->subscribed_to_news_letter)->toBe(0);
    }

    public function test_update_missing_is_subscribed_422(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $resp = $this->adminPut($admin, '/api/admin/marketing/subscribers/'.$s->id, []);
        expect($resp->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_id_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/marketing/subscribers/9999999', ['is_subscribed' => false]);
        expect($resp->getStatusCode())->toBe(404);
    }

    // -------------------------------------------------------------- Delete

    public function test_delete_subscriber(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSubscriber();

        $resp = $this->adminDelete($admin, '/api/admin/marketing/subscribers/'.$s->id);
        expect(in_array($resp->getStatusCode(), [200, 204]))->toBeTrue();
        expect(SubscribersList::find($s->id))->toBeNull();
    }

    public function test_delete_unsubscribes_linked_customer(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->seedCustomer();
        $customer->subscribed_to_news_letter = 1;
        $customer->save();
        $s = $this->seedSubscriber([
            'is_subscribed' => 1,
            'customer_id'   => $customer->id,
            'email'         => $customer->email,
        ]);

        $this->adminDelete($admin, '/api/admin/marketing/subscribers/'.$s->id)->assertOk();
        expect(SubscribersList::find($s->id))->toBeNull();
        expect((int) Customer::find($customer->id)->subscribed_to_news_letter)->toBe(0);
    }

    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/marketing/subscribers/9999999');
        expect($resp->getStatusCode())->toBe(404);
    }
}
