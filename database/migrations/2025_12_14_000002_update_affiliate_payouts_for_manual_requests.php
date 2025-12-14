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
        Schema::table('affiliate_payouts', function (Blueprint $table) {
            // Add new columns for payout request system
            $table->string('request_type')->default('auto')->after('amount'); // auto or manual
            $table->text('admin_note')->nullable()->after('status');
            $table->timestamp('requested_at')->nullable()->after('admin_note');
            $table->timestamp('processed_at')->nullable()->after('requested_at');
            $table->foreignId('processed_by')->nullable()->after('processed_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_payouts', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn([
                'request_type',
                'admin_note',
                'requested_at',
                'processed_at',
                'processed_by'
            ]);
        });
    }
};
