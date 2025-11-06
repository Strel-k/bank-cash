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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_wallet_id');
            $table->unsignedBigInteger('receiver_wallet_id');
            $table->decimal('amount', 15, 2);
            $table->enum('transaction_type', ['send', 'receive', 'topup', 'withdraw', 'pay_bills']);
            $table->string('reference_number', 50)->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->foreign('sender_wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->foreign('receiver_wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
