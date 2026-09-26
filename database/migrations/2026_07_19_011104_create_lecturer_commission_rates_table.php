<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An override on top of the platform-wide default rate (Setting
     * "commission.default_percentage"). course_id null means "applies to all
     * of this lecturer's courses"; a row with course_id set overrides that
     * for one course specifically (spec section 22).
     */
    public function up(): void
    {
        Schema::create('lecturer_commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('percentage'); // percentage, fixed
            $table->decimal('value', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_commission_rates');
    }
};
