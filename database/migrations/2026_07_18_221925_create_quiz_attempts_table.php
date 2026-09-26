<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * time_limit_expires_at is computed once at start (started_at + quiz's
     * time_limit_minutes) so the deadline is enforced server-side even if the
     * quiz's time limit changes later or the client never reports back (spec
     * section 16: "Do not rely only on client-side timers").
     */
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->string('status')->default('in_progress'); // in_progress, submitted, graded
            $table->json('question_order')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('time_limit_expires_at')->nullable();
            $table->timestamp('integrity_acknowledged_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score_points', 8, 2)->nullable();
            $table->decimal('max_points', 8, 2)->nullable();
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'user_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
