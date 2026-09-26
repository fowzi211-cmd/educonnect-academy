<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One optional row per course configuring what "complete" means (spec
     * section 17). A course with no row here has no automatic completion
     * tracking — lecturers opt in by configuring criteria.
     */
    public function up(): void
    {
        Schema::create('course_completion_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('min_lessons_percent')->nullable();
            $table->unsignedTinyInteger('min_video_watch_percent')->nullable();
            $table->unsignedTinyInteger('min_attendance_percent')->nullable();
            $table->boolean('require_assignments')->default(false);
            $table->boolean('require_passing_assessments')->default(false);
            $table->boolean('require_payment_good_standing')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_completion_criteria');
    }
};
