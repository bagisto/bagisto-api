<?php

namespace Webkul\BagistoApi\Tests\Feature;

use ReflectionClassConstant;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportDownload;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportStats;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class AdminPermissionResolutionTest extends BagistoApiTest
{
    /**
     * An admin without a role has no permission.
     */
    public function test_admin_without_role_is_refused(): void
    {
        expect(AdminAuthHelper::hasPermission((object) ['role' => null], 'sales.orders'))->toBeFalse();
    }

    /**
     * An all-access role passes every key.
     */
    public function test_all_access_role_passes(): void
    {
        expect(AdminAuthHelper::hasPermission($this->admin('all', []), 'sales.orders'))->toBeTrue();
    }

    /**
     * A custom role passes only the keys it holds, stored as an array or a comma list.
     */
    public function test_custom_role_passes_only_granted_keys(): void
    {
        expect(AdminAuthHelper::hasPermission($this->admin('custom', ['sales.orders']), 'sales.orders'))->toBeTrue();
        expect(AdminAuthHelper::hasPermission($this->admin('custom', 'sales.orders, sales.invoices'), 'sales.invoices'))->toBeTrue();
        expect(AdminAuthHelper::hasPermission($this->admin('custom', ['sales.orders']), 'sales.invoices'))->toBeFalse();
    }

    /**
     * Import stats and downloads use the core `import` key, not the listing key.
     */
    public function test_import_stats_and_download_use_the_import_key(): void
    {
        $map = (new ReflectionClassConstant(AdminReadPermissions::class, 'MAP'))->getValue();

        expect($map[AdminSettingsDataTransferImportStats::class])->toBe('settings.data_transfer.imports.import');
        expect($map[AdminSettingsDataTransferImportDownload::class])->toBe('settings.data_transfer.imports.import');
        expect($map[AdminSettingsDataTransferImport::class])->toBe('settings.data_transfer.imports');
    }

    /**
     * Admin.
     */
    private function admin(?string $type, array|string $permissions): object
    {
        return (object) ['role' => (object) [
            'permission_type' => $type,
            'permissions' => $permissions,
        ]];
    }
}
