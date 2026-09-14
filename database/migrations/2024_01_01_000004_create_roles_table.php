<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A small, fixed catalog of roles shared by every organization —
        // "owner" in household A and "owner" in household B are the same
        // row, they just get attached to different org_user memberships.
        // This is our hand-rolled replacement for a roles/permissions
        // package: plain tables, plain Eloquent relationships.
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // owner | editor | contributor | viewer
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
