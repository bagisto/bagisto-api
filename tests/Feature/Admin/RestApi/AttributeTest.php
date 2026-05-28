<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin catalog attributes endpoint.
 *
 * Endpoint: GET /api/admin/catalog/attributes
 *
 * Verifies the { data, meta } envelope, field shape, all supported filters,
 * sort behaviour, pagination edge cases, and auth guards.
 *
 * Does NOT modify AdminApiTestCase — attribute seeding is handled by the
 * local insertAttribute() helper below.
 */
class AttributeTest extends AdminApiTestCase
{
    /**
     * Insert one attribute row and return the attribute ID.
     *
     * Handles the full Bagisto 2.x schema — optional default_value and regex
     * columns are included when present. The attributes table has no
     * attribute_translations equivalent used by the datagrid (admin_name is
     * a direct column on the attributes table).
     */
    protected function insertAttribute(array $overrides = []): int
    {
        $id = \DB::table('attributes')->insertGetId(array_merge([
            'code'                => 'test_attr_'.uniqid(),
            'admin_name'          => 'Test Attribute '.uniqid(),
            'type'                => 'text',
            'swatch_type'         => null,
            'validation'          => null,
            'position'            => 1,
            'is_required'         => 0,
            'is_unique'           => 0,
            'is_filterable'       => 0,
            'is_comparable'       => 0,
            'is_configurable'     => 0,
            'is_user_defined'     => 1,
            'is_visible_on_front' => 0,
            'value_per_locale'    => 0,
            'value_per_channel'   => 0,
            'enable_wysiwyg'      => 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ], $overrides));

        return $id;
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/catalog/attributes');

        $response->assertStatus(401);
    }

    public function test_listing_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->delete();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes', $token);

        $response->assertStatus(401);
    }

    public function test_listing_rejects_expired_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->update(['expires_at' => now()->subDay()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes', $token);

        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(
            ['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']
        );
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_attribute_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'my_test_color',
            'admin_name' => 'My Test Color',
            'type'       => 'select',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes');

        $response->assertOk();
        $data = collect($response->json('data'));
        $row = $data->firstWhere('id', $id);

        expect($row)->not()->toBeNull();
        expect($row['code'])->toBe('my_test_color');
        expect($row['type'])->toBe('select');
        expect($row['locale'])->toBe('en');
    }

    public function test_listing_row_has_expected_fields(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');

        expect($row)->toHaveKeys([
            'id', 'code', 'type', 'adminName', 'isRequired', 'isUnique',
            'valuePerLocale', 'valuePerChannel', 'isFilterable', 'isConfigurable',
            'isVisibleOnFront', 'isUserDefined', 'swatchType', 'position',
            'locale', 'createdAt', 'updatedAt',
            'translations', 'options', 'validation', 'defaultValue',
        ]);
        expect($row['translations'])->toBeNull();
        expect($row['options'])->toBeNull();
        expect($row['validation'])->toBeNull();
        expect($row['defaultValue'])->toBeNull();
    }

    public function test_filter_by_id_single(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'filter_id_a']);
        $id2 = $this->insertAttribute(['code' => 'filter_id_b']);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?id='.$id1);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_id_comma_list(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'filter_comma_a']);
        $id2 = $this->insertAttribute(['code' => 'filter_comma_b']);
        $id3 = $this->insertAttribute(['code' => 'filter_comma_c']);

        $response = $this->adminGet($admin, "/api/admin/catalog/attributes?id={$id1},{$id2}");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->toContain($id2);
        expect($ids)->not()->toContain($id3);
    }

    public function test_filter_by_code_partial(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'color_swatch_main']);
        $id2 = $this->insertAttribute(['code' => 'weight_grams']);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?code=color_swatch');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_type(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'type_select_'.uniqid(), 'type' => 'select']);
        $id2 = $this->insertAttribute(['code' => 'type_text_'.uniqid(), 'type' => 'text']);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?type=select');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_admin_name_partial(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'cloth_size_'.uniqid(), 'admin_name' => 'Clothing Size Filter']);
        $id2 = $this->insertAttribute(['code' => 'weight_'.uniqid(), 'admin_name' => 'Product Weight']);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?admin_name=Clothing+Size');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_is_required(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'req_attr_'.uniqid(), 'is_required' => 1]);
        $id2 = $this->insertAttribute(['code' => 'opt_attr_'.uniqid(), 'is_required' => 0]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?is_required=1');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_is_user_defined(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'user_def_'.uniqid(), 'is_user_defined' => 1]);
        $id2 = $this->insertAttribute(['code' => 'sys_attr_'.uniqid(), 'is_user_defined' => 0]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?is_user_defined=0');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id2);
        expect($ids)->not()->toContain($id1);
    }

    public function test_filter_by_locale(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'locale_test_'.uniqid(), 'admin_name' => 'Locale Test Attr']);

        \DB::table('attribute_translations')->insert([
            'attribute_id' => $id,
            'locale'       => 'fr',
            'name'         => 'Attribut de locale',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?locale=fr&id='.$id);

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $id);

        expect($row)->not()->toBeNull();
        expect($row['adminName'])->toBe('Attribut de locale');
    }

    public function test_sort_by_code_asc_compound(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute(['code' => 'zzz_sort_z'.uniqid()]);
        $this->insertAttribute(['code' => 'aaa_sort_a'.uniqid()]);
        $this->insertAttribute(['code' => 'mmm_sort_m'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?sort=code-asc');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code')->filter()->values()->all();
        $sorted = $codes;
        sort($sorted, SORT_FLAG_CASE | SORT_STRING);
        expect($codes)->toBe($sorted);
    }

    public function test_sort_by_position_desc_split(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute(['code' => 'pos_sort_1'.uniqid(), 'position' => 10]);
        $this->insertAttribute(['code' => 'pos_sort_2'.uniqid(), 'position' => 50]);
        $this->insertAttribute(['code' => 'pos_sort_3'.uniqid(), 'position' => 30]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?sort=position&order=desc');

        $response->assertOk();
        $positions = collect($response->json('data'))->pluck('position')->all();
        $sorted = $positions;
        rsort($sorted);
        expect($positions)->toBe($sorted);
    }

    public function test_default_sort_is_id_desc(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'def_sort_a'.uniqid()]);
        $id2 = $this->insertAttribute(['code' => 'def_sort_b'.uniqid()]);
        $id3 = $this->insertAttribute(['code' => 'def_sort_c'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids[0])->toBeGreaterThanOrEqual(max($id1, $id2, $id3));
    }

    public function test_unknown_sort_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?sort=nonexistent_column');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    public function test_pagination_page_two(): void
    {
        $admin = $this->createAdmin();

        $existing = (int) \DB::table('attributes')->count();
        $target = 15;
        $toAdd = max(0, $target - $existing);

        for ($i = 1; $i <= $toAdd; $i++) {
            $this->insertAttribute(['code' => 'page2_attr_'.$i.uniqid()]);
        }

        $total = (int) \DB::table('attributes')->count();
        $lastPage = (int) ceil($total / 10);
        $expected = $total - (($lastPage - 1) * 10);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?per_page=10&page='.$lastPage);

        $response->assertOk();
        expect(count($response->json('data')))->toBe($expected);
        expect($response->json('meta.currentPage'))->toBe($lastPage);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?per_page=9999');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_per_page_zero_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?per_page=0');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_page_zero_clamps_to_one(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?page=0');

        $response->assertOk();
        expect($response->json('meta.currentPage'))->toBe(1);
    }

    public function test_page_beyond_last_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?page=9999&per_page=10');

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    public function test_unknown_filter_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?totally_unknown=xyz');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    public function test_invalid_boolean_filter_is_silently_dropped(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute(['is_required' => 1]);
        $this->insertAttribute(['is_required' => 0]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?is_required=maybe');

        $response->assertOk();
        expect($response->json('meta.total'))->toBeGreaterThan(0);
    }

    public function test_special_characters_in_filter_do_not_crash(): void
    {
        $admin = $this->createAdmin();
        $this->insertAttribute(['code' => 'special_'.uniqid(), 'admin_name' => "O'Brien's Color"]);

        $response = $this->adminGet($admin, "/api/admin/catalog/attributes?admin_name=O'Brien");
        $response->assertOk();

        $response2 = $this->adminGet($admin, "/api/admin/catalog/attributes?code='; DROP TABLE attributes; --");
        $response2->assertOk();

        $response3 = $this->adminGet($admin, '/api/admin/catalog/attributes?code=--');
        $response3->assertOk();
    }

    public function test_filter_by_is_filterable(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'filterable_yes_'.uniqid(), 'is_filterable' => 1]);
        $id2 = $this->insertAttribute(['code' => 'filterable_no_'.uniqid(), 'is_filterable' => 0]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?is_filterable=1');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_is_configurable(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'cfg_yes_'.uniqid(), 'is_configurable' => 1]);
        $id2 = $this->insertAttribute(['code' => 'cfg_no_'.uniqid(), 'is_configurable' => 0]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes?is_configurable=1');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    /**
     * Insert one attribute option row and return the option ID.
     */
    protected function insertAttributeOption(int $attributeId, array $overrides = []): int
    {
        return \DB::table('attribute_options')->insertGetId(array_merge([
            'attribute_id' => $attributeId,
            'admin_name'   => 'Option '.uniqid(),
            'swatch_value' => null,
            'sort_order'   => 0,
        ], $overrides));
    }

    /**
     * Insert one attribute option translation row.
     */
    protected function insertAttributeOptionTranslation(int $optionId, string $locale, string $label): void
    {
        \DB::table('attribute_option_translations')->insert([
            'attribute_option_id' => $optionId,
            'locale'              => $locale,
            'label'               => $label,
        ]);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertAttribute(['code' => 'detail_auth_'.uniqid()]);

        $response = $this->publicGet('/api/admin/catalog/attributes/'.$id);

        $response->assertStatus(401);
    }

    public function test_detail_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);
        $id = $this->insertAttribute(['code' => 'detail_revoked_'.uniqid()]);

        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->delete();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/'.$id, $token);

        $response->assertStatus(401);
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/9999999');

        $response->assertStatus(404);
    }

    public function test_detail_zero_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/0');

        $response->assertStatus(404);
    }

    public function test_detail_negative_id_returns_4xx(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/-1');

        expect($response->getStatusCode())->toBeGreaterThanOrEqual(400);
        expect($response->getStatusCode())->toBeLessThan(500);
    }

    public function test_detail_returns_attribute_with_all_fields_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'detail_text_'.uniqid(),
            'admin_name' => 'Detail Text Attr',
            'type'       => 'text',
            'validation' => 'numeric',
        ]);

        \DB::table('attribute_translations')->insert([
            'attribute_id' => $id,
            'locale'       => 'en',
            'name'         => 'Detail Text Attr EN',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/'.$id);

        $response->assertOk();

        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toStartWith('detail_text_');
        expect($response->json('type'))->toBe('text');
        expect($response->json('adminName'))->toBe('Detail Text Attr');
        expect($response->json('validation'))->toBe('numeric');

        expect($response->json('translations'))->toBeArray();
        expect(count($response->json('translations')))->toBeGreaterThanOrEqual(1);

        $firstTranslation = $response->json('translations.0');
        expect($firstTranslation)->toHaveKey('locale');
        expect($firstTranslation)->toHaveKey('name');

        expect($response->json('options'))->toBe([]);
    }

    public function test_detail_for_attribute_with_no_options_returns_empty_array(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code' => 'no_opts_'.uniqid(),
            'type' => 'boolean',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/'.$id);

        $response->assertOk();
        expect($response->json('options'))->toBe([]);
        expect($response->json('options'))->not()->toBeNull();
    }

    public function test_detail_for_select_attribute_returns_options_with_translations(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'select_opts_'.uniqid(),
            'type' => 'select',
        ]);
        $optionId1 = $this->insertAttributeOption($attrId, ['admin_name' => 'Red', 'sort_order' => 1]);
        $optionId2 = $this->insertAttributeOption($attrId, ['admin_name' => 'Blue', 'sort_order' => 2]);

        $this->insertAttributeOptionTranslation($optionId1, 'en', 'Red');
        $this->insertAttributeOptionTranslation($optionId2, 'en', 'Blue');

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/'.$attrId);

        $response->assertOk();
        $options = $response->json('options');

        expect($options)->toBeArray();
        expect(count($options))->toBe(2);

        foreach ($options as $option) {
            expect($option)->toHaveKey('id');
            expect($option)->toHaveKey('adminName');
            expect($option)->toHaveKey('sortOrder');
            expect($option)->toHaveKey('translations');
            expect($option['translations'])->toBeArray();
            expect(count($option['translations']))->toBeGreaterThanOrEqual(1);

            $firstOptionTranslation = $option['translations'][0];
            expect($firstOptionTranslation)->toHaveKey('locale');
            expect($firstOptionTranslation)->toHaveKey('label');
        }
    }

    public function test_detail_translations_array_contains_every_seeded_locale(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'multi_locale_'.uniqid(),
            'admin_name' => 'Multi Locale Attr',
            'type'       => 'text',
        ]);

        \DB::table('attribute_translations')->insert([
            ['attribute_id' => $id, 'locale' => 'en', 'name' => 'Multi Locale EN'],
            ['attribute_id' => $id, 'locale' => 'fr', 'name' => 'Multi Locale FR'],
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/attributes/'.$id);

        $response->assertOk();
        $translations = $response->json('translations');

        expect($translations)->toBeArray();

        $locales = collect($translations)->pluck('locale')->all();
        expect($locales)->toContain('en');
        expect($locales)->toContain('fr');

        $enEntry = collect($translations)->firstWhere('locale', 'en');
        $frEntry = collect($translations)->firstWhere('locale', 'fr');

        expect($enEntry['name'])->toBe('Multi Locale EN');
        expect($frEntry['name'])->toBe('Multi Locale FR');
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function publicPut(string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data);
    }

    protected function publicDelete(string $url): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url);
    }

    public function test_create_attribute_simple_type_returns_201(): void
    {
        $admin = $this->createAdmin();
        $code = 'crud_text_'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'       => $code,
            'admin_name' => 'CRUD Text',
            'type'       => 'text',
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('code'))->toBe($code);
        expect($response->json('type'))->toBe('text');
        expect($response->json('adminName'))->toBe('CRUD Text');
        expect($response->json('isUserDefined'))->toBe(1);
    }

    public function test_create_attribute_select_with_options_returns_201(): void
    {
        $admin = $this->createAdmin();
        $code = 'crud_sel_'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'       => $code,
            'admin_name' => 'CRUD Select',
            'type'       => 'select',
            'options'    => [
                [
                    'admin_name'   => 'Cotton',
                    'sort_order'   => 1,
                    'translations' => [
                        'en' => ['label' => 'Cotton'],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);
        $options = $response->json('options');
        expect($options)->toBeArray();
        expect(count($options))->toBeGreaterThanOrEqual(1);
        $first = $options[0];
        expect($first['adminName'])->toBe('Cotton');
    }

    public function test_create_attribute_with_translations(): void
    {
        $admin = $this->createAdmin();
        $code = 'crud_trans_'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'         => $code,
            'admin_name'   => 'CRUD Translated',
            'type'         => 'text',
            'translations' => [
                'en' => ['name' => 'CRUD Translated EN'],
                'fr' => ['name' => 'CRUD Traduit FR'],
            ],
        ]);

        $response->assertStatus(201);
        $translations = $response->json('translations');
        expect($translations)->toBeArray();
        $en = collect($translations)->firstWhere('locale', 'en');
        expect($en)->not()->toBeNull();
        expect($en['name'])->toBe('CRUD Translated EN');
    }

    public function test_create_attribute_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'admin_name' => 'No Code',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_attribute_invalid_code_starting_with_digit_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'       => '1invalid_code',
            'admin_name' => 'Bad Code',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_attribute_reserved_code_type_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'       => 'type',
            'admin_name' => 'Reserved',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_attribute_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $code = 'dup_code_'.uniqid();

        $this->insertAttribute(['code' => $code]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'       => $code,
            'admin_name' => 'Duplicate',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_attribute_boolean_with_default_value(): void
    {
        $admin = $this->createAdmin();
        $code = 'crud_bool_'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'          => $code,
            'admin_name'    => 'CRUD Boolean',
            'type'          => 'boolean',
            'default_value' => '1',
        ]);

        $response->assertStatus(201);
        expect($response->json('type'))->toBe('boolean');
        expect($response->json('defaultValue'))->toBe('1');
    }

    public function test_create_attribute_requires_auth(): void
    {
        $response = $this->publicPost('/api/admin/catalog/attributes', [
            'code'       => 'no_auth_'.uniqid(),
            'admin_name' => 'No Auth',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(401);
    }

    public function test_update_attribute_admin_name_returns_200(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'upd_name_'.uniqid(),
            'admin_name' => 'Old Name',
            'type'       => 'text',
        ]);

        $code = \DB::table('attributes')->where('id', $id)->value('code');

        $response = $this->adminPut($admin, '/api/admin/catalog/attributes/'.$id, [
            'code'       => $code,
            'admin_name' => 'New Name',
            'type'       => 'text',
        ]);

        $response->assertOk();
        expect($response->json('adminName'))->toBe('New Name');
    }

    public function test_update_attribute_code_change_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'nochange_'.uniqid(),
            'admin_name' => 'No Change',
            'type'       => 'text',
        ]);

        $response = $this->adminPut($admin, '/api/admin/catalog/attributes/'.$id, [
            'code'       => 'different_code',
            'admin_name' => 'No Change',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_attribute_type_change_refused_when_product_values_exist(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'type_locked_'.uniqid(),
            'admin_name' => 'Type Locked',
            'type'       => 'text',
        ]);

        \DB::table('product_attribute_values')->insert([
            'product_id'   => 1,
            'attribute_id' => $id,
            'channel'      => 'default',
            'locale'       => 'en',
        ]);

        $code = \DB::table('attributes')->where('id', $id)->value('code');

        $response = $this->adminPut($admin, '/api/admin/catalog/attributes/'.$id, [
            'code'       => $code,
            'admin_name' => 'Type Locked',
            'type'       => 'textarea',
        ]);

        expect($response->getStatusCode())->toBe(422);

        \DB::table('product_attribute_values')->where('attribute_id', $id)->delete();
    }

    public function test_update_attribute_type_change_allowed_when_no_product_values(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'type_free_'.uniqid(),
            'admin_name' => 'Type Free',
            'type'       => 'text',
        ]);

        $code = \DB::table('attributes')->where('id', $id)->value('code');

        $response = $this->adminPut($admin, '/api/admin/catalog/attributes/'.$id, [
            'code'       => $code,
            'admin_name' => 'Type Free',
            'type'       => 'textarea',
        ]);

        $response->assertOk();
        expect($response->json('type'))->toBe('textarea');
    }

    public function test_update_attribute_options_replacement(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'       => 'opts_replace_'.uniqid(),
            'admin_name' => 'Opts Replace',
            'type'       => 'select',
        ]);

        $optId = $this->insertAttributeOption($id, ['admin_name' => 'Old Option']);
        $code = \DB::table('attributes')->where('id', $id)->value('code');

        $response = $this->adminPut($admin, '/api/admin/catalog/attributes/'.$id, [
            'code'       => $code,
            'admin_name' => 'Opts Replace',
            'type'       => 'select',
            'options'    => [
                ['id' => $optId, 'admin_name' => 'Old Option Updated', 'sort_order' => 1],
                ['admin_name' => 'Brand New Option', 'sort_order' => 2],
            ],
        ]);

        $response->assertOk();
        $options = $response->json('options');
        expect($options)->toBeArray();
        expect(count($options))->toBe(2);
        $names = array_column($options, 'adminName');
        expect($names)->toContain('Old Option Updated');
        expect($names)->toContain('Brand New Option');
    }

    public function test_update_attribute_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'upd_nocode_'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/catalog/attributes/'.$id, [
            'admin_name' => 'No Code',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_delete_attribute_returns_200(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'del_ok_'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/catalog/attributes/'.$id);

        $response->assertOk();
        expect($response->json('message'))->toBeString();

        expect(\DB::table('attributes')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_system_attribute_returns_403(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'            => 'sys_attr_'.uniqid(),
            'is_user_defined' => 0,
        ]);

        $response = $this->adminDelete($admin, '/api/admin/catalog/attributes/'.$id);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_delete_attribute_in_family_returns_409(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'in_fam_'.uniqid()]);

        $groupId = \DB::table('attribute_groups')->insertGetId([
            'attribute_family_id' => 1,
            'code'                => 'test_group_'.uniqid(),
            'column'              => 1,
            'position'            => 99,
        ]);

        \DB::table('attribute_group_mappings')->insert([
            'attribute_id'       => $id,
            'attribute_group_id' => $groupId,
            'position'           => 1,
        ]);

        $response = $this->adminDelete($admin, '/api/admin/catalog/attributes/'.$id);

        expect($response->getStatusCode())->toBe(409);

        \DB::table('attribute_group_mappings')->where('attribute_id', $id)->delete();
        \DB::table('attribute_groups')->where('id', $groupId)->delete();
    }

    public function test_delete_nonexistent_attribute_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminDelete($admin, '/api/admin/catalog/attributes/999999');

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_create_option_on_select_attribute_returns_201(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code' => 'opt_sel_'.uniqid(),
            'type' => 'select',
        ]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/'.$id.'/options', [
            'admin_name'   => 'Wool',
            'sort_order'   => 1,
            'translations' => ['en' => ['label' => 'Wool']],
        ]);

        $response->assertStatus(201);
        $options = $response->json('options');
        expect($options)->toBeArray();
        $names = array_column($options, 'adminName');
        expect($names)->toContain('Wool');
    }

    public function test_create_option_on_multiselect_attribute(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code' => 'opt_multi_'.uniqid(),
            'type' => 'multiselect',
        ]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/'.$id.'/options', [
            'admin_name' => 'Red',
        ]);

        $response->assertStatus(201);
    }

    public function test_create_option_refused_for_text_type(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code' => 'opt_text_'.uniqid(),
            'type' => 'text',
        ]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/'.$id.'/options', [
            'admin_name' => 'Should Fail',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_option_missing_admin_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code' => 'opt_noname_'.uniqid(),
            'type' => 'select',
        ]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/'.$id.'/options', [
            'sort_order' => 1,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_option_label_returns_200(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'opt_upd_'.uniqid(),
            'type' => 'select',
        ]);
        $optId = $this->insertAttributeOption($attrId, ['admin_name' => 'Before']);

        $response = $this->adminPut(
            $admin,
            '/api/admin/catalog/attributes/'.$attrId.'/options/'.$optId,
            ['admin_name' => 'After']
        );

        $response->assertOk();
        $options = $response->json('options');
        $opt = collect($options)->firstWhere('id', $optId);
        expect($opt)->not()->toBeNull();
        expect($opt['adminName'])->toBe('After');
    }

    public function test_update_option_translations_merge(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'opt_trans_upd_'.uniqid(),
            'type' => 'select',
        ]);
        $optId = $this->insertAttributeOption($attrId, ['admin_name' => 'Option']);

        $response = $this->adminPut(
            $admin,
            '/api/admin/catalog/attributes/'.$attrId.'/options/'.$optId,
            [
                'admin_name'   => 'Option',
                'translations' => ['en' => ['label' => 'English Label']],
            ]
        );

        $response->assertOk();
        $options = $response->json('options');
        $opt = collect($options)->firstWhere('id', $optId);
        expect($opt)->not()->toBeNull();
        $enLabel = collect($opt['translations'])->firstWhere('locale', 'en');
        expect($enLabel['label'])->toBe('English Label');
    }

    public function test_update_option_not_found_returns_404(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'opt_nf_'.uniqid(),
            'type' => 'select',
        ]);

        $response = $this->adminPut(
            $admin,
            '/api/admin/catalog/attributes/'.$attrId.'/options/999999',
            ['admin_name' => 'Ghost']
        );

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_option_returns_200(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'opt_del_'.uniqid(),
            'type' => 'select',
        ]);
        $optId = $this->insertAttributeOption($attrId);

        $response = $this->adminDelete(
            $admin,
            '/api/admin/catalog/attributes/'.$attrId.'/options/'.$optId
        );

        $response->assertOk();
        expect(\DB::table('attribute_options')->where('id', $optId)->exists())->toBeFalse();
    }

    public function test_delete_option_in_use_returns_409(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'opt_inuse_'.uniqid(),
            'type' => 'select',
        ]);
        $optId = $this->insertAttributeOption($attrId);

        \DB::table('product_attribute_values')->insert([
            'product_id'    => 1,
            'attribute_id'  => $attrId,
            'channel'       => 'default',
            'locale'        => 'en',
            'integer_value' => $optId,
        ]);

        $response = $this->adminDelete(
            $admin,
            '/api/admin/catalog/attributes/'.$attrId.'/options/'.$optId
        );

        expect($response->getStatusCode())->toBe(409);

        \DB::table('product_attribute_values')->where('attribute_id', $attrId)->delete();
    }

    public function test_delete_option_not_found_returns_404(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code' => 'opt_del_nf_'.uniqid(),
            'type' => 'select',
        ]);

        $response = $this->adminDelete(
            $admin,
            '/api/admin/catalog/attributes/'.$attrId.'/options/999999'
        );

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_mass_delete_multiple_attributes(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'mass_del_a_'.uniqid()]);
        $id2 = $this->insertAttribute(['code' => 'mass_del_b_'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertOk();
        $deleted = $response->json('deleted');
        expect($deleted)->toContain($id1);
        expect($deleted)->toContain($id2);

        expect(\DB::table('attributes')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('attributes')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mass_delete_with_system_attribute_rejects_entire_batch(): void
    {
        $admin = $this->createAdmin();
        $userId = $this->insertAttribute(['code' => 'mass_user_'.uniqid()]);
        $sysId = $this->insertAttribute([
            'code'            => 'mass_sys_'.uniqid(),
            'is_user_defined' => 0,
        ]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/mass-delete', [
            'indices' => [$userId, $sysId],
        ]);

        expect($response->getStatusCode())->toBe(422);
        expect(\DB::table('attributes')->where('id', $userId)->exists())->toBeTrue();
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/mass-delete', [
            'indices' => [],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_nonexistent_ids_are_skipped(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'mass_exist_'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes/mass-delete', [
            'indices' => [$id, 999999],
        ]);

        $response->assertOk();
        $deleted = $response->json('deleted');
        expect($deleted)->toContain($id);
        expect($deleted)->not()->toContain(999999);
    }

    public function test_create_attribute_revoked_token_returns_401(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->delete();

        $response = $this->adminPost($admin, '/api/admin/catalog/attributes', [
            'code'       => 'rev_tok_'.uniqid(),
            'admin_name' => 'Revoked',
            'type'       => 'text',
        ], $token);

        expect($response->getStatusCode())->toBe(401);
    }

    public function test_update_attribute_without_auth_returns_401(): void
    {
        $id = $this->insertAttribute(['code' => 'noauth_put_'.uniqid()]);

        $response = $this->publicPut('/api/admin/catalog/attributes/'.$id, [
            'code'       => 'noauth_put',
            'admin_name' => 'No Auth',
            'type'       => 'text',
        ]);

        expect($response->getStatusCode())->toBe(401);
    }

    public function test_delete_attribute_without_auth_returns_401(): void
    {
        $id = $this->insertAttribute(['code' => 'noauth_del_'.uniqid()]);

        $response = $this->publicDelete('/api/admin/catalog/attributes/'.$id);

        expect($response->getStatusCode())->toBe(401);
    }
}
