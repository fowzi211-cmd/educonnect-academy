<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('declared_amount', 10, 2)->nullable()->after('receipt_name');
            $table->string('bank_reference_number')->nullable()->unique()->after('declared_amount');
            $table->string('receipt_reference_number')->nullable()->unique()->after('bank_reference_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['declared_amount', 'bank_reference_number', 'receipt_reference_number']);
        });
    }
};
