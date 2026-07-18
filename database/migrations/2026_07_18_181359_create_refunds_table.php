<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('reason');
            $table->string('status')->default('completed'); // completed (TestGateway settles synchronously)
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('ends_access_immediately')->default(false);
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
