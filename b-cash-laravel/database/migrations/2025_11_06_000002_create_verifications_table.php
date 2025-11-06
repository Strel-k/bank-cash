<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->string('document_number');
            $table->string('document_front_path')->nullable();
            $table->string('document_back_path')->nullable();
            $table->string('face_image_path')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->text('details')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
        Schema::dropIfExists('verifications');
    }
};