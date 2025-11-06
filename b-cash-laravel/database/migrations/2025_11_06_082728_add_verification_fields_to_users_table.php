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
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('password_hash');
            $table->date('birthdate')->nullable()->after('pin');
            $table->text('address')->nullable()->after('birthdate');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('address');
            $table->enum('id_type', ['national_id', 'drivers_license', 'passport'])->nullable()->after('gender');
            $table->string('id_front')->nullable()->after('id_type');
            $table->string('id_back')->nullable()->after('id_front');
            $table->string('face_image')->nullable()->after('id_back');
            $table->integer('registration_step')->default(1)->after('is_verified');
            $table->dropColumn('pin_hash'); // Remove old pin_hash column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin_hash')->nullable()->after('password_hash'); // Restore old pin_hash column
            $table->dropColumn([
                'pin',
                'birthdate',
                'address',
                'gender',
                'id_type',
                'id_front',
                'id_back',
                'face_image',
                'registration_step'
            ]);
        });
    }
};
