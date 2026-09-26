<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Covers quizzes, examinations, practice tests, and surveys (spec section
     * 16) — all share the same question/attempt engine and are distinguished
     * by the "type" column and how grading is treated (surveys are ungraded).
     */
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('quiz'); // quiz, exam, practice_test, survey
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('max_attempts')->nullable();
            $table->decimal('pass_mark_percent', 5, 2)->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('negative_marking')->default(false);
            $table->string('result_visibility')->default('immediate'); // immediate, after_feedback_release, manual
            $table->string('correct_answer_visibility')->default('after_submit'); // never, after_submit, after_close, after_feedback_release
            $table->timestamp('feedback_release_at')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->text('integrity_acknowledgement_text')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
