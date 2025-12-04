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
    Schema::create('referral_tracks', function (Blueprint $table) {
        $table->id();
        $table->string('prospect_email', 120)->nullable();
        $table->string('prospect_telegram_id', 50)->nullable();
        $table->string('prospect_ip', 50)->nullable();
        $table->string('ref_code', 20);
        $table->timestamps();

        $table->unique('prospect_email');
        $table->unique('prospect_telegram_id');
        $table->index('ref_code');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_tracks');
    }
};
