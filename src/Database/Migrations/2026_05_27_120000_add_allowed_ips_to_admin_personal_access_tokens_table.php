<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_personal_access_tokens', function (Blueprint $table) {
            $table->text('allowed_ips')
                ->nullable()
                ->after('rate_limit_per_day')
                ->comment('JSON array of allowed IPv4/IPv6/CIDR entries. NULL = any IP allowed.');
        });
    }

    public function down(): void
    {
        Schema::table('admin_personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('allowed_ips');
        });
    }
};
