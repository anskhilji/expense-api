<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // who logged it
            $table->foreignId('category_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->date('spent_at');
            $table->timestamps();

            // The two queries this table exists to answer fast: "this
            // category's spend this month" and "this org's ledger for a
            // date range".
            $table->index(['category_id', 'spent_at']);
            $table->index(['organization_id', 'spent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
