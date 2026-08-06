<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Setting\Models\Language;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->json('description_translatable')->nullable()->after('description');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('invoice_items')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                DB::table('invoice_items')->where('id', $row->id)->update([
                    'description_translatable' => $row->description !== null
                        ? json_encode([$defaultLocale => $row->description])
                        : null,
                ]);
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->renameColumn('description_translatable', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('description_scalar', 255)->nullable()->after('description');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('invoice_items')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                $translations = $row->description !== null ? (json_decode((string) $row->description, true) ?? []) : [];

                DB::table('invoice_items')->where('id', $row->id)->update([
                    'description_scalar' => $translations[$defaultLocale] ?? (reset($translations) ?: null),
                ]);
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->renameColumn('description_scalar', 'description');
        });
    }
};
