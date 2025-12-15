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
        // Add commission_rate to affiliates table
        if (Schema::hasTable('affiliates') && !Schema::hasColumn('affiliates', 'commission_rate')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->decimal('commission_rate', 5, 2)->default(30.00)->after('total_sales'); // Default 30%
                $table->decimal('total_commission', 15, 2)->default(0)->after('commission_rate');
            });
        }

        // Add sale_id to orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'sale_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_id')->nullable()->after('moota_tagging_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('affiliates', 'commission_rate')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->dropColumn(['commission_rate', 'total_commission']);
            });
        }

        if (Schema::hasColumn('orders', 'sale_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('sale_id');
            });
        }
    }
};
