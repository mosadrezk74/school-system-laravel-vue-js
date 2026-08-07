<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_method');
            $table->text('notes')->nullable()->after('reference_number');
        });

        DB::table('payments')->whereNull('paid_at')->update([
            'paid_at' => DB::raw('payment_date'),
        ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['collected_by']);
            $table->dropIndex(['payment_date', 'status']);
            $table->dropUnique(['payment_number']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_number',
                'payment_date',
                'transaction_id',
                'gateway_response',
                'status',
                'collected_by',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_number', 50)->nullable()->after('student_id');
            $table->date('payment_date')->nullable()->after('amount');
            $table->string('transaction_id', 255)->nullable()->after('reference_number');
            $table->json('gateway_response')->nullable()->after('transaction_id');
            $table->string('status', 15)->default('pending')->after('gateway_response');
            $table->unsignedBigInteger('collected_by')->nullable()->after('status');
        });

        DB::table('payments')->whereNull('payment_date')->update([
            'payment_date' => DB::raw('DATE(paid_at)'),
        ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('payment_number');
            $table->index(['payment_date', 'status']);
            $table->foreign('collected_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'notes']);
        });
    }
};
