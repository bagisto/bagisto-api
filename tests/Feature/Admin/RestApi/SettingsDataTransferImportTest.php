<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;
use ZipArchive;

class SettingsDataTransferImportTest extends AdminApiTestCase
{
    /**
     * Test listing requires admin token.
     */
    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports');
        $response->assertStatus(401);
    }

    /**
     * Test listing returns envelope.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    /**
     * Test listing row shape.
     */
    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport([
            'type' => 'product',
            'action' => 'append',
            'state' => 'pending',
        ]);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'code', 'action', 'state', 'createdAt']);
    }

    /**
     * Test filter by code.
     */
    public function test_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['type' => 'product']);
        $b = $this->insertImport(['type' => 'customer']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?code=customer');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    /**
     * Test filter by type alias.
     */
    public function test_filter_by_type_alias(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['type' => 'product']);
        $b = $this->insertImport(['type' => 'category']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?type=category');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    /**
     * Test filter by action.
     */
    public function test_filter_by_action(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['action' => 'append']);
        $b = $this->insertImport(['action' => 'delete']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?action=delete');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    /**
     * Test filter by state.
     */
    public function test_filter_by_state(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['state' => 'pending']);
        $b = $this->insertImport(['state' => 'completed']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?state=completed');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    /**
     * Test filter by created at range.
     */
    public function test_filter_by_created_at_range(): void
    {
        $admin = $this->createAdmin();
        $old = $this->insertImport([
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);
        $new = $this->insertImport();

        $from = now()->subDays(2)->toDateString();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?created_at_from='.$from);
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($new);
        expect($ids)->not->toContain($old);
    }

    /**
     * Test sort id desc default.
     */
    public function test_sort_id_desc_default(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport();
        $b = $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?per_page=2');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids[0])->toBe($b);
    }

    /**
     * Test detail requires admin token.
     */
    public function test_detail_requires_admin_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(401);
    }

    /**
     * Test detail happy path.
     */
    public function test_detail_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport([
            'type' => 'product',
            'state' => 'pending',
        ]);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toBe('product');
        expect($response->json('state'))->toBe('pending');
        expect($response->json('filePath'))->toBeString();
    }

    /**
     * Test detail not found.
     */
    public function test_detail_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/999999');
        $response->assertStatus(404);
    }

    /**
     * Test cancel pending happy path.
     */
    public function test_cancel_pending_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'pending']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('state'))->toBe('cancelled');

        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    /**
     * Test cancel processing happy path.
     */
    public function test_cancel_processing_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'processing']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    /**
     * Test cancel completed is refused.
     */
    public function test_cancel_completed_is_refused(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'completed']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertStatus(422);
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('completed');
    }

    /**
     * Test cancel not found.
     */
    public function test_cancel_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/cancel');
        $response->assertStatus(404);
    }

    /**
     * Test cancel requires token.
     */
    public function test_cancel_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/cancel');
        $response->assertStatus(401);
    }

    /**
     * Test cancel requires permission.
     */
    public function test_cancel_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');
        $response->assertStatus(403);
    }

    /**
     * Test delete happy path.
     */
    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();

        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/'.$id);

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->exists())->toBeFalse();
    }

    /**
     * Test delete not found.
     */
    public function test_delete_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/999999');
        $response->assertStatus(404);
    }

    /**
     * Test delete requires token.
     */
    public function test_delete_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->deleteJson('/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(401);
    }

    /**
     * Test delete requires permission.
     */
    public function test_delete_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(403);
    }

    /**
     * Test create happy path.
     */
    public function test_create_happy_path(): void
    {
        Storage::fake('private');

        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(),
            ['file' => $this->sampleCsv()],
        ));

        $response->assertStatus(201);
        expect($response->json('code'))->toBe('products');
        expect($response->json('state'))->toBe('pending');

        $id = (int) $response->json('id');
        $row = \DB::table('imports')->where('id', $id)->first();
        expect($row)->not->toBeNull();
        expect($row->type)->toBe('products');
        Storage::disk('private')->assertExists($row->file_path);
    }

    /**
     * Test create missing file returns 422.
     */
    public function test_create_missing_file_returns_422(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', $this->createPayload());
        $response->assertStatus(422);
    }

    /**
     * Test create with a directory source needs the directory.
     */
    public function test_create_with_a_directory_source_needs_the_directory(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $payload = $this->createPayload();
        unset($payload['images_directory_path']);

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $payload,
            ['file' => $this->sampleCsv()],
        ));

        $response->assertStatus(422);
    }

    /**
     * Test create with a url source needs neither directory nor archive.
     */
    public function test_create_with_a_url_source_needs_neither_directory_nor_archive(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $payload = $this->createPayload(['image_source' => 'url']);
        unset($payload['images_directory_path']);

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $payload,
            ['file' => $this->sampleCsv()],
        ));

        $response->assertStatus(201);
        expect($response->json('imageSource'))->toBe('url');
    }

    /**
     * Test create with an upload source unpacks the archive.
     */
    public function test_create_with_an_upload_source_unpacks_the_archive(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['image_source' => 'upload']),
            [
                'file' => $this->sampleCsv(),
                'upload_images' => $this->sampleImagesArchive(),
            ],
        ));

        $response->assertStatus(201);
        expect($response->json('imageSource'))->toBe('upload');
        expect($response->json('imagesArchiveName'))->toBe('images.zip');

        $id = (int) $response->json('id');

        Storage::disk('private')->assertExists('imports/'.$id.'/images/shirt.jpg');
        Storage::disk('private')->assertMissing('imports/'.$id.'/images/images.zip');
    }

    /**
     * Only the image entries of an uploaded archive are unpacked.
     */
    public function test_create_with_an_upload_source_unpacks_only_images(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['image_source' => 'upload']),
            [
                'file' => $this->sampleCsv(),
                'upload_images' => $this->imagesArchive([
                    'shirt.jpg' => 'not-really-a-jpeg',
                    'nested/hat.png' => 'not-really-a-png',
                    'shell.php' => '<?php echo 1;',
                    'page.html' => '<script>alert(1)</script>',
                    'notes.txt' => 'plain text',
                ]),
            ],
        ));

        $response->assertStatus(201);

        $directory = 'imports/'.(int) $response->json('id').'/images';

        Storage::disk('private')->assertExists($directory.'/shirt.jpg');
        Storage::disk('private')->assertExists($directory.'/nested/hat.png');
        Storage::disk('private')->assertMissing($directory.'/shell.php');
        Storage::disk('private')->assertMissing($directory.'/page.html');
        Storage::disk('private')->assertMissing($directory.'/notes.txt');
    }

    /**
     * Archive entries whose names climb out of the images directory are skipped.
     */
    public function test_create_with_an_upload_source_skips_traversal_entries(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['image_source' => 'upload']),
            [
                'file' => $this->sampleCsv(),
                'upload_images' => $this->imagesArchive([
                    'safe.jpg' => 'not-really-a-jpeg',
                    '../escaped.jpg' => 'not-really-a-jpeg',
                    '../../escaped-twice.jpg' => 'not-really-a-jpeg',
                    'dir\\backslash.jpg' => 'not-really-a-jpeg',
                ]),
            ],
        ));

        $response->assertStatus(201);

        $files = Storage::disk('private')->allFiles();

        expect($files)->toContain('imports/'.(int) $response->json('id').'/images/safe.jpg');
        expect(collect($files)->filter(fn ($path) => str_contains($path, 'escaped') || str_contains($path, 'backslash'))->all())->toBe([]);
    }

    /**
     * An archive holding more files than core allows is refused before any import is created.
     */
    public function test_create_rejects_an_archive_over_the_entry_cap(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $entries = [];

        for ($index = 0; $index <= 5000; $index++) {
            $entries['image-'.$index.'.jpg'] = 'x';
        }

        $importsBefore = \DB::table('imports')->count();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['image_source' => 'upload']),
            [
                'file' => $this->sampleCsv(),
                'upload_images' => $this->imagesArchive($entries),
            ],
        ));

        $response->assertStatus(422);

        expect($response->json('detail') ?? $response->json('message'))->toBe(trans('bagistoapi::app.admin.settings.data-transfer.import.images-archive-too-large', [
            'entries' => 5000,
            'size' => 1024,
        ]));
        expect(\DB::table('imports')->count())->toBe($importsBefore);
        expect(Storage::disk('private')->allFiles())->toBe([]);
    }

    /**
     * Test create with an upload source needs the archive.
     */
    public function test_create_with_an_upload_source_needs_the_archive(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['image_source' => 'upload']),
            ['file' => $this->sampleCsv()],
        ));

        $response->assertStatus(422);
    }

    /**
     * Test create invalid type returns 422.
     */
    public function test_create_invalid_type_returns_422(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['type' => 'nonsense']),
            ['file' => $this->sampleCsv()],
        ));
        $response->assertStatus(422);
    }

    /**
     * Test create invalid action returns 422.
     */
    public function test_create_invalid_action_returns_422(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['action' => 'bogus']),
            ['file' => $this->sampleCsv()],
        ));
        $response->assertStatus(422);
    }

    /**
     * Test create invalid validation strategy returns 422.
     */
    public function test_create_invalid_validation_strategy_returns_422(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(['validation_strategy' => 'bogus']),
            ['file' => $this->sampleCsv()],
        ));
        $response->assertStatus(422);
    }

    /**
     * Test create requires token.
     */
    public function test_create_requires_token(): void
    {
        Storage::fake('private');

        $response = $this->call(
            'POST',
            '/api/admin/settings/data-transfer/imports',
            array_merge($this->createPayload(), ['file' => $this->sampleCsv()]),
            [],
            ['file' => $this->sampleCsv()],
            $this->transformHeadersToServerVars(['Accept' => 'application/json']),
        );
        $response->assertStatus(401);
    }

    /**
     * Test create requires permission.
     */
    public function test_create_requires_permission(): void
    {
        Storage::fake('private');
        $admin = $this->createAdminWithoutPermissions();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', array_merge(
            $this->createPayload(),
            ['file' => $this->sampleCsv()],
        ));
        $response->assertStatus(403);
    }

    /* ------------------------------------------------------------------ */
    /* Update */
    /* ------------------------------------------------------------------ */

    /**
     * Test update resets state to pending.
     */
    public function test_update_resets_state_to_pending(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();
        $id = $this->insertImport([
            'state' => 'completed',
            'processed_rows_count' => 5,
        ]);

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports/'.$id, array_merge(
            $this->createPayload(['action' => 'append']),
            [
                'file' => $this->sampleCsv(),
                '_method' => 'PUT',
            ],
        ));

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
        expect((int) \DB::table('imports')->where('id', $id)->value('processed_rows_count'))->toBe(0);
    }

    /**
     * Test update requires token.
     */
    public function test_update_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->putJson('/api/admin/settings/data-transfer/imports/'.$id, $this->createPayload());
        $response->assertStatus(401);
    }

    /**
     * Test update requires permission.
     */
    public function test_update_requires_permission(): void
    {
        Storage::fake('private');
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports/'.$id, array_merge(
            $this->createPayload(),
            ['_method' => 'PUT'],
        ));
        $response->assertStatus(403);
    }

    /**
     * Test update not found.
     */
    public function test_update_not_found(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports/999999', array_merge(
            $this->createPayload(),
            ['_method' => 'PUT'],
        ));
        $response->assertStatus(404);
    }

    /* ------------------------------------------------------------------ */
    /* Validate */
    /* ------------------------------------------------------------------ */

    /**
     * Test validate not found.
     */
    public function test_validate_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/validate');
        $response->assertStatus(404);
    }

    /**
     * Test validate requires token.
     */
    public function test_validate_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/validate');
        $response->assertStatus(401);
    }

    /**
     * Test validate requires permission.
     */
    public function test_validate_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/validate');
        $response->assertStatus(403);
    }

    /* ------------------------------------------------------------------ */
    /* Start / Link / Index — guard paths */
    /* ------------------------------------------------------------------ */

    /**
     * Test start nothing to import returns 400.
     */
    public function test_start_nothing_to_import_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/start');
        $response->assertStatus(400);
    }

    /**
     * Test start not found.
     */
    public function test_start_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/start');
        $response->assertStatus(404);
    }

    /**
     * Test start requires token.
     */
    public function test_start_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/start');
        $response->assertStatus(401);
    }

    /**
     * Test start requires permission.
     */
    public function test_start_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/start');
        $response->assertStatus(403);
    }

    /**
     * Test link nothing to import returns 400.
     */
    public function test_link_nothing_to_import_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/link');
        $response->assertStatus(400);
    }

    /**
     * Test link not found.
     */
    public function test_link_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/link');
        $response->assertStatus(404);
    }

    /**
     * Test link requires token.
     */
    public function test_link_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/link');
        $response->assertStatus(401);
    }

    /**
     * Test index nothing to import returns 400.
     */
    public function test_index_nothing_to_import_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/index');
        $response->assertStatus(400);
    }

    /**
     * Test index not found.
     */
    public function test_index_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/index');
        $response->assertStatus(404);
    }

    /**
     * Test index requires token.
     */
    public function test_index_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/index');
        $response->assertStatus(401);
    }

    /* ------------------------------------------------------------------ */
    /* Stats */
    /* ------------------------------------------------------------------ */

    /**
     * Test stats happy path.
     */
    public function test_stats_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/stats');
        $response->assertOk();
        expect($response->json('stats'))->toBeArray();
        expect($response->json('id'))->toBe($id);
    }

    /**
     * Test stats requires core import key.
     */
    public function test_stats_requires_core_import_key(): void
    {
        $viewer = Role::create([
            'name' => 'Viewer '.uniqid(),
            'description' => 'imports only',
            'permission_type' => 'custom',
            'permissions' => ['settings', 'settings.data_transfer', 'settings.data_transfer.imports'],
        ]);
        $importer = Role::create([
            'name' => 'Importer '.uniqid(),
            'description' => 'import key',
            'permission_type' => 'custom',
            'permissions' => ['settings', 'settings.data_transfer', 'settings.data_transfer.imports.import'],
        ]);
        $id = $this->insertImport();
        $url = '/api/admin/settings/data-transfer/imports/'.$id.'/stats';

        $this->adminGet($this->createAdmin(['role_id' => $viewer->id]), $url)->assertStatus(403);
        $this->adminGet($this->createAdmin(['role_id' => $importer->id]), $url)->assertOk();
    }

    /**
     * Test stats not found.
     */
    public function test_stats_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/999999/stats');
        $response->assertStatus(404);
    }

    /**
     * Test stats requires token.
     */
    public function test_stats_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports/'.$id.'/stats');
        $response->assertStatus(401);
    }

    /**
     * Test download streams file.
     */
    public function test_download_streams_file(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $path = 'imports/dl-'.uniqid().'.csv';
        Storage::disk('private')->put($path, "sku,name\nps4,Sample\n");
        $id = $this->insertImport(['file_path' => $path]);

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/download');
        $response->assertOk();
        expect($response->headers->get('Content-Disposition'))->toContain('attachment');
    }

    /**
     * Test download missing file returns 404.
     */
    public function test_download_missing_file_returns_404(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();
        $id = $this->insertImport(['file_path' => 'imports/does-not-exist-'.uniqid().'.csv']);

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/download');
        $response->assertStatus(404);
    }

    /**
     * Test download error report missing returns 404.
     */
    public function test_download_error_report_missing_returns_404(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();
        $id = $this->insertImport(['error_file_path' => null]);

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/download-error-report');
        $response->assertStatus(404);
    }

    /**
     * Test download error report streams when present.
     */
    public function test_download_error_report_streams_when_present(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $path = 'imports/err-'.uniqid().'.csv';
        Storage::disk('private')->put($path, "row,error\n1,bad\n");
        $id = $this->insertImport(['error_file_path' => $path]);

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/download-error-report');
        $response->assertOk();
    }

    /**
     * Test download sample streams for valid type format.
     */
    public function test_download_sample_streams_for_valid_type_format(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();

        $samplePath = config('importers.products.sample_paths.csv');
        Storage::put($samplePath, "sku,name\nps4,Sample\n");

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/sample/products/csv');
        $response->assertOk();
        expect($response->headers->get('Content-Disposition'))->toContain('attachment');
    }

    /**
     * Test download sample invalid format returns 422.
     */
    public function test_download_sample_invalid_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/sample/products/pdf');
        $response->assertStatus(422);
    }

    /**
     * Test download sample unknown type returns 404.
     */
    public function test_download_sample_unknown_type_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/sample/nonsense/csv');
        $response->assertStatus(404);
    }

    /**
     * Test cancel allowed for custom role with core import key.
     */
    public function test_cancel_allowed_for_custom_role_with_core_import_key(): void
    {
        $role = Role::create([
            'name' => 'Importer '.uniqid(),
            'description' => 'core import key',
            'permission_type' => 'custom',

            'permissions' => [
                'settings',
                'settings.data_transfer',
                'settings.data_transfer.imports',
                'settings.data_transfer.imports.import',
            ],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $id = $this->insertImport(['state' => 'pending']);

        $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel')->assertOk();

        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    /**
     * Test validate is not refused for custom role with core import key.
     */
    public function test_validate_is_not_refused_for_custom_role_with_core_import_key(): void
    {
        $role = Role::create([
            'name' => 'Importer '.uniqid(),
            'description' => 'core import key',
            'permission_type' => 'custom',

            'permissions' => [
                'settings',
                'settings.data_transfer',
                'settings.data_transfer.imports',
                'settings.data_transfer.imports.import',
            ],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $id = $this->insertImport(['state' => 'pending']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/validate');

        expect($response->getStatusCode())->not->toBe(403);
    }

    /**
     * Test cancel refused for custom role with only edit key.
     */
    public function test_cancel_refused_for_custom_role_with_only_edit_key(): void
    {
        $role = Role::create([
            'name' => 'Editor '.uniqid(),
            'description' => 'edit only',
            'permission_type' => 'custom',

            'permissions' => [
                'settings',
                'settings.data_transfer',
                'settings.data_transfer.imports',
                'settings.data_transfer.imports.edit',
            ],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $id = $this->insertImport(['state' => 'pending']);

        $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel')->assertStatus(403);
    }

    /**
     * Insert import.
     */
    protected function insertImport(array $overrides = []): int
    {
        return (int) \DB::table('imports')->insertGetId(array_merge([
            'state' => 'pending',
            'process_in_queue' => 1,
            'type' => 'product',
            'action' => 'append',
            'validation_strategy' => 'stop-on-errors',
            'allowed_errors' => 0,
            'processed_rows_count' => 0,
            'invalid_rows_count' => 0,
            'errors_count' => 0,
            'field_separator' => ',',
            'file_path' => 'imports/sample-'.uniqid().'.csv',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    /**
     * Admin delete.
     */
    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Create admin without permissions.
     */
    protected function createAdminWithoutPermissions(): Admin
    {
        $role = Role::create([
            'name' => 'NoDataTransfer '.uniqid(),
            'description' => 'no perms',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    /* ------------------------------------------------------------------ */
    /* Create */
    /* ------------------------------------------------------------------ */

    /**
     * Sample csv.
     */
    protected function sampleCsv(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'products.csv',
            "sku,type,name\nps4,simple,Sample\n",
        );
    }

    /**
     * Sample images archive.
     */
    protected function sampleImagesArchive(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'imgs').'.zip';

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('shirt.jpg', 'not-really-a-jpeg');
        $zip->close();

        return new UploadedFile($path, 'images.zip', 'application/zip', null, true);
    }

    /**
     * Build a zip archive upload out of the given entry names and contents.
     *
     * @param  array<string, string>  $entries
     */
    protected function imagesArchive(array $entries): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'imgs').'.zip';

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($entries as $name => $contents) {
            $zip->addFromString($name, $contents);
        }

        $zip->close();

        return new UploadedFile($path, 'images.zip', 'application/zip', null, true);
    }

    /**
     * Create payload.
     */
    protected function createPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'products',
            'action' => 'append',
            'validation_strategy' => 'stop-on-errors',
            'allowed_errors' => 0,
            'field_separator' => ',',
            'image_source' => 'directory',
            'images_directory_path' => 'product-images',
        ], $overrides);
    }

    /**
     * Admin multipart.
     */
    protected function adminMultipart(Admin $admin, string $method, string $url, array $data): TestResponse
    {
        $files = array_filter($data, fn ($v) => $v instanceof UploadedFile);
        $params = array_filter($data, fn ($v) => ! $v instanceof UploadedFile);

        return $this->call(
            $method,
            $url,
            $params,
            [],
            $files,
            $this->transformHeadersToServerVars(array_merge(
                $this->adminHeaders($admin),
                ['Accept' => 'application/json'],
            )),
        );
    }

    /* ------------------------------------------------------------------ */
    /* Downloads (binary, REST-only) */
    /* ------------------------------------------------------------------ */

    /**
     * Admin binary get.
     */
    protected function adminBinaryGet(Admin $admin, string $url): TestResponse
    {
        return $this->get($url, array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/octet-stream'],
        ));
    }
}
