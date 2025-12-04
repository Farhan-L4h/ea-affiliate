<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('referral_tracks', function (Blueprint $table) {
            $table->string('prospect_name')->nullable()->after('id');
            $table->string('prospect_phone', 30)->nullable()->after('prospect_email');
            $table->string('prospect_telegram_username', 100)->nullable()->after('prospect_telegram_id');
            $table->string('status', 20)->default('clicked')->after('ref_code');
        });
    }

    public function down(): void
    {
        Schema::table('referral_tracks', function (Blueprint $table) {
            $table->dropColumn([
                'prospect_name',
                'prospect_phone',
                'prospect_telegram_username',
                'status',
            ]);
        });
    }
};
