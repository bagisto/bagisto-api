<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Settings → Data Transfer Imports (Block B Wave 3).
 *
 * Endpoints:
 *   GET    /api/admin/settings/data-transfer/imports
 *   GET    /api/admin/settings/data-transfer/imports/{id}
 *   POST   /api/admin/settings/data-transfer/imports/{id}/cancel
 *   DELETE /api/admin/settings/data-transfer/imports/{id}
 */
class SettingsDataTransferImportTest extends AdminApiTestCase
{
    protected function insertImport(array $overrides = []): int
    {
        return (int) \DB::table('imports')->insertGetId(array_merge([
            'state'                => 'pending',
            'process_in_queue'     => 1,
            'type'                 => 'product',
            'action'               => 'append',
            'validation_strategy'  => 'stop-on-errors',
            'allowed_errors'       => 0,
            'processed_rows_count' => 0,
            'invalid_rows_count'   => 0,
            'errors_count'         => 0,
            'field_separator'      => ',',
            'file_path'            => 'imports/sample-'.uniqid().'.csv',
            'created_at'           => now(),
            'updated_at'           => now(),
        ], $overrides));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'NoDataTransfer '.uniqid(),
            'description'     => 'no perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport(['type' => 'product', 'action' => 'append', 'state' => 'pending']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'code', 'action', 'state', 'createdAt']);
    }

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

    public function test_filter_by_created_at_range(): void
    {
        $admin = $this->createAdmin();
        $old = $this->insertImport(['created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()]);
        $new = $this->insertImport();

        $from = now()->subDays(2)->toDateString();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?created_at_from='.$from);
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($new);
        expect($ids)->not->toContain($old);
    }

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

    public function test_detail_requires_admin_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(401);
    }

    public function test_detail_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['type' => 'product', 'state' => 'pending']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toBe('product');
        expect($response->json('state'))->toBe('pending');
        expect($response->json('filePath'))->toBeString();
    }

    public function test_detail_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/999999');
        $response->assertStatus(404);
    }

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

    public function test_cancel_processing_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'processing']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    public function test_cancel_completed_is_refused(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'completed']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertStatus(422);
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('completed');
    }

    public function test_cancel_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/cancel');
        $response->assertStatus(404);
    }

    public function test_cancel_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/cancel');
        $response->assertStatus(401);
    }

    public function test_cancel_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();

        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/'.$id);

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->deleteJson('/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(403);
    }

    /* ------------------------------------------------------------------ */
    /* Create */
    /* ------------------------------------------------------------------ */

    protected function sampleCsv(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'products.csv',
            "sku,type,name\nps4,simple,Sample\n",
        );
    }

    protected function createPayload(array $overrides = []): array
    {
        return array_merge([
            'type'                => 'products',
            'action'              => 'append',
            'validation_strategy' => 'stop-on-errors',
            'allowed_errors'      => 0,
            'field_separator'     => ',',
        ], $overrides);
    }

    protected function adminMultipart(\Webkul\User\Models\Admin $admin, string $method, string $url, array $data): TestResponse
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

    public function test_create_missing_file_returns_422(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports', $this->createPayload());
        $response->assertStatus(422);
    }

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

    public function test_update_resets_state_to_pending(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'completed', 'processed_rows_count' => 5]);

        $response = $this->adminMultipart($admin, 'POST', '/api/admin/settings/data-transfer/imports/'.$id, array_merge(
            $this->createPayload(['action' => 'append']),
            ['file' => $this->sampleCsv(), '_method' => 'PUT'],
        ));

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('pending');
        expect((int) \DB::table('imports')->where('id', $id)->value('processed_rows_count'))->toBe(0);
    }

    public function test_update_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->putJson('/api/admin/settings/data-transfer/imports/'.$id, $this->createPayload());
        $response->assertStatus(401);
    }

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

    public function test_validate_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/validate');
        $response->assertStatus(404);
    }

    public function test_validate_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/validate');
        $response->assertStatus(401);
    }

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

    public function test_start_nothing_to_import_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/start');
        $response->assertStatus(400);
    }

    public function test_start_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/start');
        $response->assertStatus(404);
    }

    public function test_start_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/start');
        $response->assertStatus(401);
    }

    public function test_start_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/start');
        $response->assertStatus(403);
    }

    public function test_link_nothing_to_import_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/link');
        $response->assertStatus(400);
    }

    public function test_link_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/link');
        $response->assertStatus(404);
    }

    public function test_link_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/link');
        $response->assertStatus(401);
    }

    public function test_index_nothing_to_import_returns_400(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['processed_rows_count' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/index');
        $response->assertStatus(400);
    }

    public function test_index_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/index');
        $response->assertStatus(404);
    }

    public function test_index_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/index');
        $response->assertStatus(401);
    }

    /* ------------------------------------------------------------------ */
    /* Stats */
    /* ------------------------------------------------------------------ */

    public function test_stats_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/stats');
        $response->assertOk();
        expect($response->json('stats'))->toBeArray();
        expect($response->json('id'))->toBe($id);
    }

    public function test_stats_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/999999/stats');
        $response->assertStatus(404);
    }

    public function test_stats_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports/'.$id.'/stats');
        $response->assertStatus(401);
    }

    /* ------------------------------------------------------------------ */
    /* Downloads (binary, REST-only) */
    /* ------------------------------------------------------------------ */

    protected function adminBinaryGet(\Webkul\User\Models\Admin $admin, string $url): TestResponse
    {
        return $this->get($url, array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/octet-stream'],
        ));
    }

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

    public function test_download_missing_file_returns_404(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();
        // file_path is NOT NULL — point at a path that does not exist on disk.
        $id = $this->insertImport(['file_path' => 'imports/does-not-exist-'.uniqid().'.csv']);

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/download');
        $response->assertStatus(404);
    }

    public function test_download_error_report_missing_returns_404(): void
    {
        Storage::fake('private');
        $admin = $this->createAdmin();
        $id = $this->insertImport(['error_file_path' => null]);

        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/download-error-report');
        $response->assertStatus(404);
    }

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

    public function test_download_sample_invalid_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/sample/products/pdf');
        $response->assertStatus(422);
    }

    public function test_download_sample_unknown_type_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminBinaryGet($admin, '/api/admin/settings/data-transfer/imports/sample/nonsense/csv');
        $response->assertStatus(404);
    }
}
