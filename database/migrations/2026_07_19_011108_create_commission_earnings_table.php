<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per confirmed transaction (spec section 22: "based on confirmed
     * transactions and adjusted for refunds"). rate_type/rate_value are a
     * snapshot of whatever rate applied at earn time, so later rate changes
     * never rewrite history. refunded_amount is decremented by processRefund
     * proportionally; net payable = amount - refunded_amount.
     */
    public function up(): void
    {
        Schema::create('commission_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('gross_amount', 10, 2);
            $table->string('rate_type'); // percentage, fixed
            $table->decimal('rate_value', 8, 2);
            $table->decimal('amount', 10, 2);
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->string('currency');
            $table->string('status')->default('unpaid'); // unpaid, paid
            $table->foreignId('payout_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_earnings');
    }
};
