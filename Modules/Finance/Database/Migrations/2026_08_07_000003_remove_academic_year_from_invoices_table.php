<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex(['academic_year_id', 'status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('invoice_number');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['academic_year_id', 'status']);
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
        });
    }
};
