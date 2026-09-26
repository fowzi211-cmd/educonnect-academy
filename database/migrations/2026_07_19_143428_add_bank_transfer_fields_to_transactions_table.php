<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('gateway_transaction_id')->constrained()->nullOnDelete();
            $table->string('receipt_path')->nullable()->after('bank_account_id');
            $table->string('receipt_name')->nullable()->after('receipt_path');
            $table->foreignId('reviewed_by')->nullable()->after('receipt_name')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['receipt_path', 'receipt_name', 'reviewed_at']);
        });
    }
};
