<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // who logged this deposit
            $table->decimal('amount', 12, 2);
            $table->string('source')->nullable(); // "salary", "freelance", ...
            $table->date('received_at');
            $table->timestamps();

            // Fast "sum of this month's income" queries.
            $table->index(['organization_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
