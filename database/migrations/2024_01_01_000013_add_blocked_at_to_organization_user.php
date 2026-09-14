<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('organization_user', 'blocked_at')) {
            Schema::table('organization_user', function (Blueprint $table) {
                $table->timestamp('blocked_at')->nullable()->after('role_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('organization_user', 'blocked_at')) {
            Schema::table('organization_user', function (Blueprint $table) {
                $table->dropColumn('blocked_at');
            });
        }
    }
};