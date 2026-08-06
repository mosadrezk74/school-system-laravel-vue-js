<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_details', function (Blueprint $table): void {
            $table->string('notes', 500)->nullable()->after('status');
        });

        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->unsignedSmallInteger('other_count')->default(0)->after('excused_count');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_details', function (Blueprint $table): void {
            $table->dropColumn('notes');
        });

        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropColumn('other_count');
        });
    }
};
