<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create storefront_keys table with complete API key management features:
     * - Basic key storage and configuration
     * - Key type (shop vs admin) for multi-tenant support
     * - Expiration and rotation tracking
     * - Usage monitoring for compliance
     * - Soft deletes for audit trail
     */
    public function up(): void
    {
        if (Schema::hasTable('storefront_keys')) {
            return;
        }

        Schema::create('storefront_keys', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique()->comment('Human-readable key name');
            $table->enum('key_type', ['shop', 'admin'])
                ->default('shop')
                ->comment('API key type: shop (X-STOREFRONT-KEY) or admin (X-Admin-Key)');
            $table->string('key')->unique()->index()
                ->comment('The actual API key (prefixed with pk_storefront_)');
            $table->boolean('is_active')->default(true)
                ->comment('Whether this key is active and usable');

            $table->integer('rate_limit')->default(100)
                ->comment('Requests per minute allowed for this key');
            $table->json('allowed_ips')->nullable()
                ->comment('IP whitelist (JSON array) for additional security');

            $table->timestamp('expires_at')->nullable()
                ->comment('Key expiration date - after this, key is invalid');
            $table->timestamp('last_used_at')->nullable()
                ->comment('Last time this key was used - for usage tracking');
            $table->timestamp('deprecation_date')->nullable()
                ->comment('Date after which key is deprecated - used during rotation transition');
            $table->foreignId('rotated_from_id')->nullable()
                ->constrained('storefront_keys')
                ->comment('Reference to the key this was rotated from - for audit trail');

            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp - for maintaining audit history');

            $table->index('expires_at');
            $table->index('last_used_at');
            $table->index('deprecation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storefront_keys');
    }
};
