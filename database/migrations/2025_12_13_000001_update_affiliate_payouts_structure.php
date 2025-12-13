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
            // Drop old column
            $table->dropIndex(['affiliate_ref']);
            $table->dropColumn('affiliate_ref');
            $table->renameColumn('commission', 'amount');
            
            // Add new column
            $table->foreignId('affiliate_id')->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**aku
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_payouts', function (Blueprint $table) {
            // Restore old columns
            $table->dropForeign(['affiliate_id']);
            $table->dropColumn('affiliate_id');
            $table->renameColumn('amount', 'commission');
            $table->string('affiliate_ref', 20)->after('id');
            $table->index('affiliate_ref');
        });
    }
};
