<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matching questions store their correct pairs directly as JSON rather
     * than a join table — simpler to author and grade, and the pairs are
     * never shared across questions so a separate table buys nothing.
     */
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // mcq_single, mcq_multi, true_false, matching, short_answer, essay, numerical, file_upload
            $table->text('prompt');
            $table->decimal('points', 6, 2)->default(1);
            $table->unsignedInteger('position')->default(0);
            $table->string('correct_short_answer')->nullable();
            $table->decimal('correct_numerical', 12, 4)->nullable();
            $table->decimal('numerical_tolerance', 12, 4)->default(0);
            $table->json('matching_pairs')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
