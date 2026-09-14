<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            // Chosen by the user at signup, never auto-generated — unique the
            // same way email is, and re-checked in RegisterRequest for a clean
            // 422 instead of a database constraint violation.
            $table->string('name')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
