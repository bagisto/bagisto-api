<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

class AttributeFamilyTest extends AdminApiTestCase
{
    /**
     * Test listing requires admin token.
     */
    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/catalog/families');

        $response->assertStatus(401);
    }

    /**
     * Test listing rejects revoked token.
     */
    public function test_listing_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        $this->revokeAdminSessions($admin);

        $response = $this->adminGet($admin, '/api/admin/catalog/families', $token);

        $response->assertStatus(401);
    }

    /**
     * Test listing rejects expired token.
     */
    public function test_listing_rejects_expired_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        $this->expireAdminSessions($admin);

        $response = $this->adminGet($admin, '/api/admin/catalog/families', $token);

        $response->assertStatus(401);
    }

    /**
     * Test listing returns envelope for authenticated admin.
     */
    public function test_listing_returns_envelope_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(
            ['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']
        );
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    /**
     * Test listing returns seeded family row.
     */
    public function test_listing_returns_seeded_family_row(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily([
            'code' => 'my_test_family',
            'name' => 'My Test Family',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families');

        $response->assertOk();
        $data = collect($response->json('data'));
        $row = $data->firstWhere('id', $familyId);

        expect($row)->not()->toBeNull();
        expect($row['code'])->toBe('my_test_family');
        expect($row['name'])->toBe('My Test Family');
    }

    /**
     * Test listing row has expected fields.
     */
    public function test_listing_row_has_expected_fields(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');

        expect($row)->toHaveKeys(['id', 'code', 'name']);
        expect($row['attributeGroups'])->toBeNull();
    }

    /**
     * Test filter by id single.
     */
    public function test_filter_by_id_single(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertFamily(['code' => 'filter_id_a_'.uniqid()]);
        $id2 = $this->insertFamily(['code' => 'filter_id_b_'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families?id='.$id1);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    /**
     * Test filter by id comma list.
     */
    public function test_filter_by_id_comma_list(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertFamily(['code' => 'filter_comma_a_'.uniqid()]);
        $id2 = $this->insertFamily(['code' => 'filter_comma_b_'.uniqid()]);
        $id3 = $this->insertFamily(['code' => 'filter_comma_c_'.uniqid()]);

        $response = $this->adminGet($admin, "/api/admin/catalog/families?id={$id1},{$id2}");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->toContain($id2);
        expect($ids)->not()->toContain($id3);
    }

    /**
     * Test filter by code partial.
     */
    public function test_filter_by_code_partial(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertFamily(['code' => 'electronics_family_'.uniqid()]);
        $id2 = $this->insertFamily(['code' => 'clothing_family_'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families?code=electronics_family');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    /**
     * Test filter by name partial.
     */
    public function test_filter_by_name_partial(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertFamily([
            'code' => 'fam_n1_'.uniqid(),
            'name' => 'Electronics Products',
        ]);
        $id2 = $this->insertFamily([
            'code' => 'fam_n2_'.uniqid(),
            'name' => 'Clothing Items',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families?name=Electronics');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    /**
     * Test sort by code asc.
     */
    public function test_sort_by_code_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily(['code' => 'zzz_sort_z_'.uniqid()]);
        $this->insertFamily(['code' => 'aaa_sort_a_'.uniqid()]);
        $this->insertFamily(['code' => 'mmm_sort_m_'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families?sort=code-asc');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code')->filter()->values()->all();
        $sorted = $codes;
        sort($sorted);
        expect($codes)->toBe($sorted);
    }

    /**
     * Test sort by name desc.
     */
    public function test_sort_by_name_desc(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily([
            'code' => 'srt_n1_'.uniqid(),
            'name' => 'Alpha Family',
        ]);
        $this->insertFamily([
            'code' => 'srt_n2_'.uniqid(),
            'name' => 'Zeta Family',
        ]);
        $this->insertFamily([
            'code' => 'srt_n3_'.uniqid(),
            'name' => 'Middle Family',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families?sort=name&order=desc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->filter()->values()->all();
        $sorted = $names;
        rsort($sorted);
        expect($names)->toBe($sorted);
    }

    /**
     * Test default sort is id desc.
     */
    public function test_default_sort_is_id_desc(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertFamily(['code' => 'def_sort_a_'.uniqid()]);
        $id2 = $this->insertFamily(['code' => 'def_sort_b_'.uniqid()]);
        $id3 = $this->insertFamily(['code' => 'def_sort_c_'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids[0])->toBeGreaterThanOrEqual(max($id1, $id2, $id3));
    }

    /**
     * Test unknown sort falls back to default.
     */
    public function test_unknown_sort_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?sort=nonexistent_column');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    /**
     * Test pagination page two.
     */
    public function test_pagination_page_two(): void
    {
        $admin = $this->createAdmin();

        $existing = (int) \DB::table('attribute_families')->count();
        $target = 15;
        $toAdd = max(0, $target - $existing);

        for ($i = 1; $i <= $toAdd; $i++) {
            $this->insertFamily(['code' => 'page2_fam_'.$i.uniqid()]);
        }

        $total = (int) \DB::table('attribute_families')->count();
        $lastPage = (int) ceil($total / 10);
        $expected = $total - (($lastPage - 1) * 10);

        $response = $this->adminGet($admin, '/api/admin/catalog/families?per_page=10&page='.$lastPage);

        $response->assertOk();
        expect(count($response->json('data')))->toBe($expected);
        expect($response->json('meta.currentPage'))->toBe($lastPage);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    /**
     * Test per page above cap clamped.
     */
    public function test_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?per_page=9999');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test per page zero falls back to default.
     */
    public function test_per_page_zero_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?per_page=0');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBe(10);
    }

    /**
     * Test page zero clamps to one.
     */
    public function test_page_zero_clamps_to_one(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?page=0');

        $response->assertOk();
        expect($response->json('meta.currentPage'))->toBe(1);
    }

    /**
     * Test page beyond last returns empty data.
     */
    public function test_page_beyond_last_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?page=9999&per_page=10');

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    /**
     * Test detail requires admin token.
     */
    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $familyId = $this->insertFamily(['code' => 'detail_auth_'.uniqid()]);

        $response = $this->publicGet('/api/admin/catalog/families/'.$familyId);

        $response->assertStatus(401);
    }

    /**
     * Test detail rejects revoked token.
     */
    public function test_detail_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);
        $familyId = $this->insertFamily(['code' => 'detail_rev_'.uniqid()]);

        $this->revokeAdminSessions($admin);

        $response = $this->adminGet($admin, '/api/admin/catalog/families/'.$familyId, $token);

        $response->assertStatus(401);
    }

    /**
     * Test detail unknown id returns 404.
     */
    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families/9999999');

        $response->assertStatus(404);
    }

    /**
     * Test detail zero id returns 404.
     */
    public function test_detail_zero_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families/0');

        $response->assertStatus(404);
    }

    /**
     * Test detail negative id returns 4xx.
     */
    public function test_detail_negative_id_returns_4xx(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/families/-1');

        expect($response->getStatusCode())->toBeGreaterThanOrEqual(400);
        expect($response->getStatusCode())->toBeLessThan(500);
    }

    /**
     * Test detail returns family with all fields.
     */
    public function test_detail_returns_family_with_all_fields(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily([
            'code' => 'detail_happy_'.uniqid(),
            'name' => 'Detail Happy Family',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families/'.$familyId);

        $response->assertOk();
        expect($response->json('id'))->toBe($familyId);
        expect($response->json('code'))->toStartWith('detail_happy_');
        expect($response->json('name'))->toBe('Detail Happy Family');
        expect($response->json('attributeGroups'))->toBeArray();
    }

    /**
     * Test detail with attribute groups populated.
     */
    public function test_detail_with_attribute_groups_populated(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'with_grp_'.uniqid()]);
        $groupId = $this->insertGroup($familyId, [
            'code' => 'general_grp',
            'name' => 'General',
            'column' => 1,
            'position' => 1,
        ]);
        $attrId = $this->insertAttribute([
            'code' => 'grp_attr_'.uniqid(),
            'type' => 'text',
        ]);
        $this->mapAttributeToGroup($attrId, $groupId, 1);

        $response = $this->adminGet($admin, '/api/admin/catalog/families/'.$familyId);

        $response->assertOk();
        $groups = $response->json('attributeGroups');

        expect($groups)->toBeArray();
        expect(count($groups))->toBeGreaterThanOrEqual(1);

        $group = collect($groups)->firstWhere('id', $groupId);
        expect($group)->not()->toBeNull();
        expect($group)->toHaveKeys(['id', 'code', 'name', 'column', 'position', 'attributes']);
        expect($group['code'])->toBe('general_grp');
        expect($group['name'])->toBe('General');
        expect($group['column'])->toBe(1);
        expect($group['position'])->toBe(1);

        $attrs = collect($group['attributes']);
        $attr = $attrs->firstWhere('id', $attrId);
        expect($attr)->not()->toBeNull();
        expect($attr)->toHaveKeys(['id', 'code', 'type', 'isRequired', 'column', 'position']);
    }

    /**
     * Test detail empty groups returns empty array.
     */
    public function test_detail_empty_groups_returns_empty_array(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'no_groups_'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families/'.$familyId);

        $response->assertOk();
        expect($response->json('attributeGroups'))->toBe([]);
    }

    /**
     * Test detail multiple groups sorted by position.
     */
    public function test_detail_multiple_groups_sorted_by_position(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'multi_grp_'.uniqid()]);
        $this->insertGroup($familyId, [
            'name' => 'Group Z',
            'position' => 3,
            'column' => 1,
        ]);
        $this->insertGroup($familyId, [
            'name' => 'Group A',
            'position' => 1,
            'column' => 1,
        ]);
        $this->insertGroup($familyId, [
            'name' => 'Group M',
            'position' => 2,
            'column' => 1,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/families/'.$familyId);

        $response->assertOk();
        $groups = $response->json('attributeGroups');
        $positions = collect($groups)->pluck('position')->all();
        $sorted = $positions;
        sort($sorted);
        expect($positions)->toBe($sorted);
    }

    /**
     * Test unknown filter is ignored.
     */
    public function test_unknown_filter_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily();

        $response = $this->adminGet($admin, '/api/admin/catalog/families?totally_unknown=xyz');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    /**
     * Test special characters in filter do not crash.
     */
    public function test_special_characters_in_filter_do_not_crash(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily([
            'code' => 'spec_'.uniqid(),
            'name' => "O'Brien's Family",
        ]);

        $response = $this->adminGet($admin, "/api/admin/catalog/families?name=O'Brien");
        $response->assertOk();

        $response2 = $this->adminGet($admin, "/api/admin/catalog/families?code='; DROP TABLE attribute_families; --");
        $response2->assertOk();
    }

    /**
     * Test create family minimal returns 201.
     */
    public function test_create_family_minimal_returns_201(): void
    {
        $admin = $this->createAdmin();
        $code = 'fam_create_'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => $code,
            'name' => 'Created Family',
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('code'))->toBe($code);
        expect($response->json('name'))->toBe('Created Family');
        expect(\DB::table('attribute_families')->where('code', $code)->exists())->toBeTrue();
    }

    /**
     * Test create family with attribute groups and custom attributes.
     */
    public function test_create_family_with_attribute_groups_and_custom_attributes(): void
    {
        $admin = $this->createAdmin();
        $code = 'fam_grp_'.uniqid();
        $attrId1 = $this->insertAttribute(['code' => 'fam_create_a_'.uniqid()]);
        $attrId2 = $this->insertAttribute(['code' => 'fam_create_b_'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => $code,
            'name' => 'Family With Groups',

            'attribute_groups' => [
                [
                    'code' => 'general',
                    'name' => 'General',
                    'column' => 1,
                    'position' => 1,

                    'custom_attributes' => [
                        ['id' => $attrId1],
                        ['id' => $attrId2],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);
        $familyId = $response->json('id');
        expect(\DB::table('attribute_groups')->where('attribute_family_id', $familyId)->count())->toBe(1);
        $groupId = \DB::table('attribute_groups')->where('attribute_family_id', $familyId)->value('id');
        expect(\DB::table('attribute_group_mappings')->where('attribute_group_id', $groupId)->count())->toBe(2);
    }

    /**
     * Test family duplicate group name returns 422 not 500.
     */
    public function test_family_duplicate_group_name_returns_422_not_500(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => 'fam_dupgrp_'.uniqid(),
            'name' => 'Dup Group Family',

            'attribute_groups' => [
                [
                    'code' => 'general',
                    'name' => 'General',
                    'column' => 1,
                    'position' => 1,
                ], [
                    'code' => 'general_two',
                    'name' => 'General',
                    'column' => 2,
                    'position' => 2,
                ],
            ],
        ]);

        expect($response->getStatusCode())->toBe(422);
        expect(strtolower($response->getContent()))->toContain('unique');
        expect(strtolower($response->getContent()))->not->toContain('sqlstate');
    }

    /**
     * Test create family missing code returns 422.
     */
    public function test_create_family_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/families', ['name' => 'No Code']);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create family missing name returns 422.
     */
    public function test_create_family_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/families', ['code' => 'fam_no_name_'.uniqid()]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create family duplicate code returns 422.
     */
    public function test_create_family_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $code = 'fam_dup_'.uniqid();
        $this->insertFamily(['code' => $code]);

        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => $code,
            'name' => 'Duplicate',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create family invalid code returns 422.
     */
    public function test_create_family_invalid_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => '1starts_with_digit',
            'name' => 'Bad Code',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create family invalid group column returns 422.
     */
    public function test_create_family_invalid_group_column_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => 'fam_badcol_'.uniqid(),
            'name' => 'Bad Group Column',

            'attribute_groups' => [
                [
                    'code' => 'general',
                    'name' => 'General',
                    'column' => 5,
                ],
            ],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create family group missing code returns 422.
     */
    public function test_create_family_group_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/families', [
            'code' => 'fam_gnocode_'.uniqid(),
            'name' => 'Group No Code',

            'attribute_groups' => [
                [
                    'name' => 'General',
                    'column' => 1,
                ],
            ],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create family requires auth.
     */
    public function test_create_family_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/families', [
            'code' => 'fam_noauth_'.uniqid(),
            'name' => 'No Auth',
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test update family renames successfully.
     */
    public function test_update_family_renames_successfully(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily([
            'code' => 'upd_'.uniqid(),
            'name' => 'Before',
        ]);

        $code = \DB::table('attribute_families')->where('id', $familyId)->value('code');
        $response = $this->adminPut($admin, '/api/admin/catalog/families/'.$familyId, [
            'code' => $code,
            'name' => 'After Update',
        ]);

        $response->assertOk();
        expect(\DB::table('attribute_families')->where('id', $familyId)->value('name'))->toBe('After Update');
    }

    /**
     * Test update family adds new group.
     */
    public function test_update_family_adds_new_group(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'upd_newgrp_'.uniqid()]);
        $code = \DB::table('attribute_families')->where('id', $familyId)->value('code');
        $name = \DB::table('attribute_families')->where('id', $familyId)->value('name');
        $attrId = $this->insertAttribute(['code' => 'upd_attr_'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/catalog/families/'.$familyId, [
            'code' => $code,
            'name' => $name,

            'attribute_groups' => [
                'group_new_1' => [
                    'code' => 'new_group',
                    'name' => 'New Group',
                    'column' => 1,
                    'position' => 1,

                    'custom_attributes' => [
                        [
                            'id' => $attrId,
                            'position' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $groupId = \DB::table('attribute_groups')
            ->where('attribute_family_id', $familyId)
            ->where('code', 'new_group')
            ->value('id');
        expect($groupId)->not()->toBeNull();
        expect(\DB::table('attribute_group_mappings')
            ->where('attribute_group_id', $groupId)
            ->where('attribute_id', $attrId)
            ->exists())->toBeTrue();
    }

    /**
     * Test update family removes omitted group.
     */
    public function test_update_family_removes_omitted_group(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'upd_rmgrp_'.uniqid()]);
        $code = \DB::table('attribute_families')->where('id', $familyId)->value('code');
        $name = \DB::table('attribute_families')->where('id', $familyId)->value('name');
        $groupId = $this->insertGroup($familyId, ['code' => 'to_remove']);

        $response = $this->adminPut($admin, '/api/admin/catalog/families/'.$familyId, [
            'code' => $code,
            'name' => $name,
            'attribute_groups' => [],
        ]);

        $response->assertOk();
        expect(\DB::table('attribute_groups')->where('id', $groupId)->exists())->toBeFalse();
    }

    /**
     * Test update family unique code excludes self.
     */
    public function test_update_family_unique_code_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'upd_self_'.uniqid()]);
        $code = \DB::table('attribute_families')->where('id', $familyId)->value('code');

        $response = $this->adminPut($admin, '/api/admin/catalog/families/'.$familyId, [
            'code' => $code,
            'name' => 'Renamed',
        ]);

        $response->assertOk();
    }

    /**
     * Test update family duplicate code returns 422.
     */
    public function test_update_family_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertFamily(['code' => 'upd_other_'.uniqid()]);
        $code1 = \DB::table('attribute_families')->where('id', $id1)->value('code');
        $id2 = $this->insertFamily(['code' => 'upd_target_'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/catalog/families/'.$id2, [
            'code' => $code1,
            'name' => 'Stealing Code',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test update family unknown id returns 404.
     */
    public function test_update_family_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/catalog/families/9999999', [
            'code' => 'upd_nf_'.uniqid(),
            'name' => 'Nope',
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test update family requires auth.
     */
    public function test_update_family_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertFamily(['code' => 'upd_noauth_'.uniqid()]);
        $response = $this->putJson('/api/admin/catalog/families/'.$id, [
            'code' => 'x',
            'name' => 'y',
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test delete family happy path.
     */
    public function test_delete_family_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily(['code' => 'fam_keep_'.uniqid()]);
        $deleteId = $this->insertFamily(['code' => 'fam_del_'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/catalog/families/'.$deleteId);

        $response->assertOk();
        expect(\DB::table('attribute_families')->where('id', $deleteId)->exists())->toBeFalse();
    }

    /**
     * Test delete family with products returns 400.
     */
    public function test_delete_family_with_products_returns_400(): void
    {
        $admin = $this->createAdmin();

        $defaultFamilyId = \DB::table('attribute_families')->where('code', 'default')->value('id');
        if (! $defaultFamilyId) {
            $defaultFamilyId = $this->insertFamily(['code' => 'default']);
        }

        if (\DB::table('products')->where('attribute_family_id', $defaultFamilyId)->count() === 0) {
            \DB::table('products')->insert([
                'type' => 'simple',
                'attribute_family_id' => $defaultFamilyId,
                'sku' => 'fam_prod_'.uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->insertFamily(['code' => 'fam_filler_'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/catalog/families/'.$defaultFamilyId);

        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('attribute_families')->where('id', $defaultFamilyId)->exists())->toBeTrue();
    }

    /**
     * Test delete family last one returns 400.
     */
    public function test_delete_family_last_one_returns_400(): void
    {
        $admin = $this->createAdmin();

        $keepId = \DB::table('attribute_families')->min('id') ?: $this->insertFamily(['code' => 'fam_only_'.uniqid()]);
        \DB::table('products')->whereNotIn('attribute_family_id', [$keepId])->delete();
        \DB::table('attribute_families')->where('id', '!=', $keepId)->delete();

        $response = $this->adminDelete($admin, '/api/admin/catalog/families/'.$keepId);

        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('attribute_families')->where('id', $keepId)->exists())->toBeTrue();
    }

    /**
     * Test delete family unknown id returns 404.
     */
    public function test_delete_family_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/catalog/families/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test delete family requires auth.
     */
    public function test_delete_family_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertFamily(['code' => 'fam_del_noauth_'.uniqid()]);
        $response = $this->deleteJson('/api/admin/catalog/families/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Insert one attribute_families row and return the family ID.
     * The table has no timestamps ($timestamps = false on the model).
     */
    protected function insertFamily(array $overrides = []): int
    {
        return \DB::table('attribute_families')->insertGetId(array_merge([
            'code' => 'test_family_'.uniqid(),
            'name' => 'Test Family '.uniqid(),
            'status' => 1,
            'is_user_defined' => 1,
        ], $overrides));
    }

    /**
     * Insert one attribute_groups row linked to $familyId and return the group ID.
     */
    protected function insertGroup(int $familyId, array $overrides = []): int
    {
        return \DB::table('attribute_groups')->insertGetId(array_merge([
            'attribute_family_id' => $familyId,
            'code' => 'grp_'.uniqid(),
            'name' => 'Group '.uniqid(),
            'column' => 1,
            'position' => 1,
            'is_user_defined' => 1,
        ], $overrides));
    }

    /**
     * Map an attribute into an attribute_group via attribute_group_mappings.
     */
    protected function mapAttributeToGroup(int $attributeId, int $groupId, int $position = 1): void
    {
        \DB::table('attribute_group_mappings')->insertOrIgnore([
            'attribute_id' => $attributeId,
            'attribute_group_id' => $groupId,
            'position' => $position,
        ]);
    }

    /**
     * Insert one attribute row and return its ID (for group-mapping tests).
     */
    protected function insertAttribute(array $overrides = []): int
    {
        return \DB::table('attributes')->insertGetId(array_merge([
            'code' => 'fam_attr_'.uniqid(),
            'admin_name' => 'Family Attr '.uniqid(),
            'type' => 'text',
            'swatch_type' => null,
            'validation' => null,
            'position' => 1,
            'is_required' => 0,
            'is_unique' => 0,
            'is_filterable' => 0,
            'is_comparable' => 0,
            'is_configurable' => 0,
            'is_user_defined' => 1,
            'is_visible_on_front' => 0,
            'value_per_locale' => 0,
            'value_per_channel' => 0,
            'enable_wysiwyg' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    /**
     * Admin put.
     */
    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Admin delete.
     */
    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }
}
