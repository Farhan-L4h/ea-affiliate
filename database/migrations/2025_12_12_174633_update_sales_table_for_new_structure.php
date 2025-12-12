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
        Schema::table('sales', function (Blueprint $table) {
            // Drop old columns
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'affiliate_ref', 'amount', 'status']);
            
            // Add new columns
            $table->foreignId('order_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliate_id')->after('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('sale_amount', 12, 2)->after('product');
            $table->decimal('commission_percentage', 5, 2)->after('sale_amount')->default(0);
            $table->decimal('commission_amount', 12, 2)->after('commission_percentage')->default(0);
            $table->timestamp('sale_date')->after('commission_amount')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['order_id']);
            $table->dropForeign(['affiliate_id']);
            $table->dropColumn(['order_id', 'affiliate_id', 'sale_amount', 'commission_percentage', 'commission_amount', 'sale_date']);
            
            // Restore old columns
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('affiliate_ref', 20)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending');
            
            $table->index('affiliate_ref');
        });
    }
};
