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
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('affiliate_ref', 20)->nullable();
        $table->string('product', 120);
        $table->decimal('amount', 12, 2);
        $table->string('status', 20)->default('pending'); // pending, paid, failed
        $table->timestamps();

        $table->index('affiliate_ref');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
