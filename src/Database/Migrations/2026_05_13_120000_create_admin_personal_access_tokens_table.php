<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_personal_access_tokens', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('token', 64)->unique()->nullable();
            $table->string('token_preview', 16)->nullable();

            $table->enum('permission_type', ['all', 'custom', 'same_as_web'])
                ->default('custom');
            $table->json('abilities')->nullable();

            $table->unsignedInteger('rate_limit_per_minute')->nullable();
            $table->unsignedInteger('rate_limit_per_day')->nullable();

            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->enum('status', ['draft', 'active', 'revoked', 'regenerated'])
                ->default('draft');

            $table->timestamp('revoked_at')->nullable();
            $table->unsignedInteger('revoked_by_admin_id')->nullable();
            $table->foreign('revoked_by_admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->timestamp('regenerated_at')->nullable();
            $table->unsignedInteger('regenerated_by_admin_id')->nullable();
            $table->foreign('regenerated_by_admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->unsignedBigInteger('regenerated_to_id')->nullable();
            $table->foreign('regenerated_to_id')->references('id')->on('admin_personal_access_tokens')->nullOnDelete();

            $table->unsignedInteger('created_by_admin_id')->nullable();
            $table->foreign('created_by_admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['admin_id', 'status']);
            $table->index('expires_at');
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_personal_access_tokens');
    }
};
