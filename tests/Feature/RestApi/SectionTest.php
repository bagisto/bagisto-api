<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Theme\Models\Section;

class SectionTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/sections';

    // ── GET /sections (collection) ────────────────

    /**
     * Test get collection returns ok.
     */
    public function test_get_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get collection returns non empty list.
     */
    public function test_get_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test collection has expected fields.
     */
    public function test_collection_has_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('type');
        expect($first)->toHaveKey('name');
        expect($first)->toHaveKey('status');
        expect($first)->toHaveKey('sortOrder');
    }

    /**
     * Test collection id is integer.
     */
    public function test_collection_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    // ── GET /sections?type=X (filter) ────────────

    /**
     * Test type filter returns only matching type.
     */
    public function test_type_filter_returns_only_matching_type(): void
    {
        $this->seedRequiredData();

        $type = $this->firstType();
        $response = $this->publicGet($this->collectionUrl.'?type='.$type);

        $response->assertOk();
        $items = $response->json();
        expect(count($items))->toBeGreaterThan(0);

        foreach ($items as $item) {
            expect($item['type'])->toBe($type);
        }
    }

    /**
     * Test type filter footer links returns only footer links.
     */
    public function test_type_filter_footer_links_returns_only_footer_links(): void
    {
        $this->seedRequiredData();

        if (! Section::where('type', 'footer_links')->exists()) {
            $this->markTestSkipped('No footer_links sections in DB.');
        }

        $response = $this->publicGet($this->collectionUrl.'?type=footer_links');

        $response->assertOk();
        $items = $response->json();
        expect(count($items))->toBeGreaterThan(0);

        foreach ($items as $item) {
            expect($item['type'])->toBe('footer_links');
        }
    }

    /**
     * Test type filter unknown type returns empty.
     */
    public function test_type_filter_unknown_type_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?type=nonexistent_type_xyz');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    /**
     * Test type filter does not mix types.
     */
    public function test_type_filter_does_not_mix_types(): void
    {
        $this->seedRequiredData();

        $types = Section::select('type')->distinct()->pluck('type')->toArray();

        if (count($types) < 2) {
            $this->markTestSkipped('Need at least 2 distinct types to test filter isolation.');
        }

        $type1Items = $this->publicGet($this->collectionUrl.'?type='.$types[0])->json();
        $type2Items = $this->publicGet($this->collectionUrl.'?type='.$types[1])->json();

        $type1Ids = array_column($type1Items, 'id');
        $type2Ids = array_column($type2Items, 'id');

        expect(array_intersect($type1Ids, $type2Ids))->toBe([]);
    }

    // ── Pagination ────────────────────────────────────────────

    /**
     * Test default page holds every section of the channel.
     */
    public function test_default_page_holds_every_section_of_the_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();

        expect(count($response->json()))->toBe((int) $response->headers->get('X-Total-Count'));
        expect((int) $response->headers->get('X-Total-Pages'))->toBe(1);
    }

    /**
     * Test items per page limits collection size.
     */
    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        if (Section::count() < 2) {
            $this->markTestSkipped('Need at least 2 sections to test per_page.');
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=1');

        $response->assertOk();
        expect(count($response->json()))->toBe(1);
    }

    /**
     * Test page parameter returns different results.
     */
    public function test_page_parameter_returns_different_results(): void
    {
        $this->seedRequiredData();

        if (Section::count() < 2) {
            $this->markTestSkipped('Need at least 2 sections to test page parameter.');
        }

        $page1 = $this->publicGet($this->collectionUrl.'?per_page=1&page=1')->json();
        $page2 = $this->publicGet($this->collectionUrl.'?per_page=1&page=2')->json();

        expect($page1[0]['id'])->not()->toBe($page2[0]['id']);
    }

    /**
     * Test page beyond total returns empty.
     */
    public function test_page_beyond_total_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?per_page=10&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET /sections/{id} (single) ──────────────

    /**
     * Test get single returns ok.
     */
    public function test_get_single_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    /**
     * Test get single returns correct id.
     */
    public function test_get_single_returns_correct_id(): void
    {
        $this->seedRequiredData();
        $id = $this->firstId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body['id'])->toBe($id);
    }

    /**
     * Test get single returns expected fields.
     */
    public function test_get_single_returns_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('type');
        expect($body)->toHaveKey('name');
        expect($body)->toHaveKey('status');
        expect($body)->toHaveKey('sortOrder');
    }

    /**
     * Test get nonexistent returns error.
     */
    public function test_get_nonexistent_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    /**
     * Item url.
     */
    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    /**
     * First id.
     */
    private function firstId(): int
    {
        $id = Section::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No sections found. Run Bagisto seeders.');
        }

        return (int) $id;
    }

    /**
     * First type.
     */
    private function firstType(): string
    {
        $type = Section::query()->orderBy('id')->value('type');

        if (! $type) {
            $this->markTestSkipped('No sections found. Run Bagisto seeders.');
        }

        return $type;
    }
}
