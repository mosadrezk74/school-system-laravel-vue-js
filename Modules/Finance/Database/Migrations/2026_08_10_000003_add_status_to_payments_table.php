<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payments were previously only ever written by an admin at the moment
     * money changed hands, so their existence implied "paid". A parent can now
     * open a payment request before any money moves, which makes the state
     * explicit; existing rows are backfilled to `paid` to preserve that meaning.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('payment_method');
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('payment_method');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('amount');
            $table->unsignedBigInteger('requested_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('confirmed_by')->nullable()->after('requested_by');
            $table->timestamp('confirmed_at')->nullable()->after('paid_at');

            $table->index(['status']);
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->nullOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('confirmed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->change();
        });

        DB::table('payments')->update(['status' => 'paid']);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['confirmed_by']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'payment_method_id',
                'refunded_amount',
                'requested_by',
                'confirmed_by',
                'confirmed_at',
            ]);
        });
    }
};
