<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class AttributeTranslationTest extends GraphQLTestCase
{
    /**
     * The attributeTranslations query lists the attribute translations.
     */
    public function test_attribute_translations_query_lists_translations(): void
    {
        $this->seedRequiredData();

        $row = DB::table('attribute_translations')->first();

        if (! $row) {
            $this->markTestSkipped('No attribute_translations seeded.');
        }

        $response = $this->graphQL(<<<'GQL'
            query {
              attributeTranslations(first: 100) {
                totalCount
                edges {
                  node {
                    _id
                    attributeId
                    locale
                    name
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $node = collect($response->json('data.attributeTranslations.edges'))
            ->pluck('node')
            ->firstWhere('_id', (int) $row->id);

        expect($response->json('errors'))->toBeNull()
            ->and($node)->not->toBeNull()
            ->and((int) $node['attributeId'])->toBe((int) $row->attribute_id)
            ->and($node['locale'])->toBe($row->locale);
    }

    /**
     * The attributeTranslation query returns one translation by its IRI.
     */
    public function test_attribute_translation_query_returns_translation(): void
    {
        $this->seedRequiredData();

        $row = DB::table('attribute_translations')->first();

        if (! $row) {
            $this->markTestSkipped('No attribute_translations seeded.');
        }

        $response = $this->graphQL(<<<GQL
            query {
              attributeTranslation(id: "/api/shop/attribute_translations/{$row->id}") {
                _id
                attributeId
                locale
                name
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and((int) $response->json('data.attributeTranslation._id'))->toBe((int) $row->id)
            ->and((int) $response->json('data.attributeTranslation.attributeId'))->toBe((int) $row->attribute_id)
            ->and($response->json('data.attributeTranslation.locale'))->toBe($row->locale);
    }

    /**
     * The attributeTranslation query reports an error for an unknown translation.
     */
    public function test_attribute_translation_query_rejects_an_unknown_id(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(<<<'GQL'
            query {
              attributeTranslation(id: "/api/shop/attribute_translations/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.attributeTranslation'))->toBeNull();
    }
}
