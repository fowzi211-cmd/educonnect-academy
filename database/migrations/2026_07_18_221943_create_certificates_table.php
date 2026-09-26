<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshots (course title, lecturer name, duration) are copied at issue
     * time so a certificate keeps reading correctly even if the course is
     * later renamed or its lecturer changes (spec section 18).
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('student_name_snapshot');
            $table->string('course_title_snapshot');
            $table->string('lecturer_name_snapshot')->nullable();
            $table->string('course_duration_snapshot')->nullable();
            $table->text('template_text')->nullable();
            $table->timestamp('completion_date');
            $table->timestamp('issued_at');
            $table->string('status')->default('active'); // active, revoked
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reissue_of_certificate_id')->nullable()->constrained('certificates')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
