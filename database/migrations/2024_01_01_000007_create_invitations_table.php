<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users');
            $table->string('email');
            $table->foreignId('role_id')->constrained();
            $table->string('token')->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            // One pending invite per email per org at a time.
            $table->unique(['organization_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
