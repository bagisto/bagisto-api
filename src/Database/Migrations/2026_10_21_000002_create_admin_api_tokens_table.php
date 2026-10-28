<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_api_tokens', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();

            $table->unsignedInteger('created_by_admin_id')->nullable();
            $table->foreign('created_by_admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->string('name', 100);

            $table->string('jti', 64)->unique();
            $table->string('token_preview', 16)->nullable();
            $table->string('signing_kid', 64)->nullable();

            $table->string('domain', 253);
            $table->json('resolved_ips')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->unsignedInteger('domain_mismatch_count')->default(0);
            $table->timestamp('last_domain_mismatch_at')->nullable();

            $table->dateTime('expires_at')->nullable();

            $table->timestamp('last_used_at')->nullable();
            $table->string('last_ip_address', 45)->nullable();
            $table->string('last_user_agent', 1023)->nullable();
            $table->string('last_channel', 10)->nullable();

            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason', 50)->nullable();
            $table->unsignedInteger('revoked_by_admin_id')->nullable();
            $table->foreign('revoked_by_admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['admin_id', 'revoked_at']);
            $table->index('domain');
            $table->index('signing_kid');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_api_tokens');
    }
};
