<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The customer model classes the storefront API issued Sanctum tokens for.
     */
    protected const CUSTOMER_TOKENABLE_TYPES = [
        'Webkul\\Customer\\Models\\Customer',
        'Webkul\\BagistoApi\\Models\\Customer',
    ];

    /**
     * Delete the customer Sanctum tokens, which customer access tokens replace.
     */
    public function up(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')
            ->whereIn('tokenable_type', self::CUSTOMER_TOKENABLE_TYPES)
            ->delete();
    }

    /**
     * Leave the tokens deleted, since they cannot be restored.
     */
    public function down(): void {}
};
