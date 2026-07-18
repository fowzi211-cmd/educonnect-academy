<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecturer_profile_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_documents');
    }
};
