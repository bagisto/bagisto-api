<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for admin-API (integration-token) writes.
 *
 * Append-only. No foreign keys — admin / token columns are snapshots so a row
 * survives deletion of the admin or the token that made the change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_api_audits', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Groups every row produced by a single API request.
            $table->uuid('history_id')->index();

            // Per-(auditable_type, auditable_id) version number (max + 1).
            $table->unsignedInteger('version_id')->default(1);

            $table->string('event', 20); // created | updated | deleted

            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Who (snapshot, no FK).
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('admin_name')->nullable();

            // Which integration token (snapshot, no FK).
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('token_name')->nullable();

            // Request context.
            $table->string('method', 10);
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('token_id');
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_api_audits');
    }
};
