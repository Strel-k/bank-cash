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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 15)->unique();
            $table->string('email', 255)->unique()->nullable();
            $table->string('full_name', 100);
            $table->string('password_hash', 255);
            $table->string('pin_hash', 255)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->integer('login_attempts')->default(0);
            $table->timestamp('last_login_attempt')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
