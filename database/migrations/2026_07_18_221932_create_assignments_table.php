<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('max_points', 6, 2)->default(100);
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('late_policy')->default('not_allowed'); // not_allowed, allowed_with_penalty, allowed_no_penalty
            $table->decimal('late_penalty_percent_per_day', 5, 2)->nullable();
            $table->string('allowed_file_types')->nullable(); // comma-separated extensions
            $table->unsignedInteger('max_file_size_kb')->default(10240);
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
