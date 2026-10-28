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
        Schema::create('customer_api_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->unsignedInteger('channel_id');
            $table->foreign('channel_id')->references('id')->on('channels')->cascadeOnDelete();
            $table->string('name', 100)->nullable();
            $table->string('client_type', 20)->default('other');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('last_ip_address', 45)->nullable();
            $table->string('last_user_agent', 1023)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_channel', 10)->nullable();
            $table->timestamp('refresh_expires_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason', 50)->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'revoked_at']);
            $table->index('channel_id');
            $table->index('expires_at');
            $table->index('refresh_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_api_sessions');
    }
};
