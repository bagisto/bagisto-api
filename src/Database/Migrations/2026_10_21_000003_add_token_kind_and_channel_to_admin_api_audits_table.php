<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_api_audits', function (Blueprint $table) {
            $table->string('token_kind', 20)->nullable()->after('token_name');
            $table->string('channel', 10)->nullable()->after('token_kind');

            $table->index(['token_kind', 'token_id']);
            $table->index('channel');
        });

        DB::table('admin_api_audits')
            ->whereNotNull('token_id')
            ->update(['token_kind' => 'integration']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_api_audits', function (Blueprint $table) {
            $table->dropIndex(['token_kind', 'token_id']);
            $table->dropIndex(['channel']);

            $table->dropColumn(['token_kind', 'channel']);
        });
    }
};
