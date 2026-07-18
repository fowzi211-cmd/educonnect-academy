<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('promotional_video_url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('level')->nullable(); // beginner, intermediate, advanced
            $table->string('teaching_language', 10)->default('en');
            $table->string('delivery_format')->default('recorded'); // live, recorded, blended
            $table->decimal('monthly_price', 8, 2)->nullable();
            $table->decimal('one_time_price', 8, 2)->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->unsignedSmallInteger('trial_period_days')->nullable();
            $table->unsignedInteger('max_students')->nullable();
            $table->timestamp('enrolment_opens_at')->nullable();
            $table->timestamp('enrolment_closes_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('certificate_available')->default(false);
            $table->string('status')->default('draft');
            // draft, under_review, revision_requested, approved, published, unpublished, suspended, archived
            $table->text('revision_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
