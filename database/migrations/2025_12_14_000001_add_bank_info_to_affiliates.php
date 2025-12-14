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
        Schema::table('affiliates', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('affiliates', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('total_sales');
            }
            if (!Schema::hasColumn('affiliates', 'account_holder_name')) {
                $table->string('account_holder_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('affiliates', 'account_number')) {
                $table->string('account_number')->nullable()->after('account_holder_name');
            }
            if (!Schema::hasColumn('affiliates', 'total_commission')) {
                $table->decimal('total_commission', 15, 2)->default(0)->after('account_number');
            }
            if (!Schema::hasColumn('affiliates', 'available_balance')) {
                $table->decimal('available_balance', 15, 2)->default(0)->after('total_commission');
            }
            if (!Schema::hasColumn('affiliates', 'withdrawn_balance')) {
                $table->decimal('withdrawn_balance', 15, 2)->default(0)->after('available_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'account_holder_name',
                'account_number',
                'total_commission',
                'available_balance',
                'withdrawn_balance'
            ]);
        });
    }
};
