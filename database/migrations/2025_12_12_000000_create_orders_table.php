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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique(); // ORDER-xxxxx
            $table->unsignedBigInteger('user_id')->nullable(); // User jika login
            $table->string('telegram_chat_id')->nullable(); // Telegram user
            $table->string('telegram_username')->nullable();
            $table->string('affiliate_ref')->nullable(); // Dari referral link
            $table->string('product'); // Nama produk
            $table->decimal('base_amount', 15, 2); // Harga dasar
            $table->integer('unique_code'); // Kode unik 001-999
            $table->decimal('total_amount', 15, 2); // base_amount + unique_code
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->string('payment_method')->default('bank_transfer'); // BCA
            $table->text('payment_info')->nullable(); // JSON: rekening, nama, nomor
            $table->string('moota_tagging_id')->nullable(); // ID dari Moota
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable(); // 24 jam dari created
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
