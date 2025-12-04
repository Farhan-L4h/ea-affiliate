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
    Schema::create('affiliate_payouts', function (Blueprint $table) {
        $table->id();
        $table->string('affiliate_ref', 20);
        $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
        $table->decimal('commission', 12, 2);
        $table->string('status', 20)->default('pending'); // pending, paid
        $table->timestamps();

        $table->index('affiliate_ref');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
