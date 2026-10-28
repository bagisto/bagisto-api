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
        Schema::create('admin_api_two_factor_challenges', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();

            $table->string('token', 64)->unique();

            $table->string('name', 100)->nullable();
            $table->string('client_type', 20)->default('other');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1023)->nullable();

            $table->unsignedSmallInteger('failed_attempts')->default(0);

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_api_two_factor_challenges');
    }
};
