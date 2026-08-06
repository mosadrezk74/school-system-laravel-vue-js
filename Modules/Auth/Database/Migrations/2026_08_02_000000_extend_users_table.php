<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brings Laravel's starter `users` table up to the ERP schema.
 *
 * The table is extended rather than recreated because Fortify, sessions and
 * existing rows already depend on it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->ulid('ulid')->nullable()->after('id');
            $table->string('username', 100)->nullable()->after('ulid');
            $table->string('phone', 20)->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');

            $table->string('gender', 10)->nullable()->after('name');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('national_id', 20)->nullable()->after('birth_date');

            // Coarse account type. Fine-grained access is Spatie roles;
            // this exists so the UI can  without a permission lookup.
            $table->string('role', 30)->default('student')->after('national_id');
            $table->boolean('is_active')->default(true)->after('role');

            $table->string('language', 10)->default('ar')->after('is_active');
            $table->string('theme', 20)->default('light')->after('language');
            $table->string('timezone', 50)->default('Asia/Riyadh')->after('theme');

            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->text('fcm_token')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->unique('ulid');
            $table->unique('username');
            $table->unique('phone');
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropUnique(['username']);
            $table->dropUnique(['phone']);
            $table->dropIndex(['role', 'is_active']);

            $table->dropColumn([
                'ulid', 'username', 'phone', 'phone_verified_at',
                'gender', 'birth_date', 'national_id', 'role', 'is_active',
                'language', 'theme', 'timezone',
                'two_factor_secret', 'two_factor_recovery_codes',
                'two_factor_confirmed_at', 'fcm_token',
                'created_by', 'updated_by', 'deleted_by', 'deleted_at',
            ]);
        });
    }
};
