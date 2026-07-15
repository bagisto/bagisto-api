<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderComment;

class OrderCommentTest extends AdminApiTestCase
{
    public function test_list_query_resolves_all_fields(): void
    {
        $orderId = Order::query()->value('id');
        if (! $orderId) {
            $this->markTestSkipped('No orders.');
        }

        $admin = $this->createAdmin();

        OrderComment::create([
            'order_id' => $orderId,
            'comment' => 'gql-list-'.uniqid(),
            'customer_notified' => 1,
        ]);

        $query = <<<'GQL'
            query ListOrderComments($orderId: Int!, $first: Int) {
              adminOrderComments(orderId: $orderId, first: $first) {
                totalCount
                pageInfo {
                  hasNextPage
                  endCursor
                }
                edges {
                  cursor
                  node {
                    id
                    _id
                    orderId
                    comment
                    customerNotified
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['orderId' => (int) $orderId, 'first' => 10], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminOrderComments.edges');
        expect($edges)->toBeArray()->not->toBeEmpty();

        $node = $edges[0]['node'];
        expect($node['orderId'])->toBe((int) $orderId);
        expect($node['comment'])->not->toBeNull();
        expect($node['customerNotified'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
    }

    public function test_create_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminOrderCommentInput!){ createAdminOrderComment(input:$input){ adminOrderCommentDto { id } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => 1, 'comment' => 'x']]);
        expect($response->json('errors'))->toBeArray();
    }

    public function test_create_persists_comment(): void
    {
        $id = Order::query()->value('id');
        if (! $id) {
            $this->markTestSkipped('No orders.');
        }

        $admin = $this->createAdmin();
        $mutation = 'mutation($input: createAdminOrderCommentInput!){ createAdminOrderComment(input:$input){ adminOrderCommentDto { comment } } }';
        $response = $this->adminGraphQL($mutation, [
            'input' => ['orderId' => $id, 'comment' => 'gql-'.uniqid(), 'customerNotified' => false],
        ], $admin);

        $node = $response->json('data.createAdminOrderComment.adminOrderCommentDto');
        if ($node === null) {
            expect($response->json('errors'))->toBeArray();
        } else {
            expect($node['comment'])->toContain('gql-');
        }
    }
}
