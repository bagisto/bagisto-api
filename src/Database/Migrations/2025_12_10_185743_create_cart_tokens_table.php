<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_cart_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cart_id')->unique()->index();
            $table->string('token')->unique()->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();

            $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_cart_tokens');
    }
};
