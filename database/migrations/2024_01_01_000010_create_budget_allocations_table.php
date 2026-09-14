<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            // Stored as the first day of the month, e.g. 2026-09-01 — keeps
            // it a real, sortable, indexable date instead of a "2026-09"
            // string, while still meaning "this whole month".
            $table->date('month');
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();

            // One allocation per category per month — allocating again
            // updates the existing row rather than creating a duplicate.
            $table->unique(['category_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};
