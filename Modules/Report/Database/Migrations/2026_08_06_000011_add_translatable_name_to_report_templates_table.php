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
        Schema::table('report_templates', function (Blueprint $table) {
            $table->json('name_translatable')->nullable()->after('name');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('report_templates')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                DB::table('report_templates')->where('id', $row->id)->update([
                    'name_translatable' => json_encode([$defaultLocale => $row->name]),
                ]);
            }
        });

        Schema::table('report_templates', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('report_templates', function (Blueprint $table) {
            $table->renameColumn('name_translatable', 'name');
        });
    }

    public function down(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            $table->string('name_scalar', 100)->nullable()->after('name');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('report_templates')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                $translations = json_decode((string) $row->name, true) ?? [];
                $value = $translations[$defaultLocale] ?? reset($translations) ?: '';

                DB::table('report_templates')->where('id', $row->id)->update([
                    'name_scalar' => $value,
                ]);
            }
        });

        Schema::table('report_templates', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('report_templates', function (Blueprint $table) {
            $table->renameColumn('name_scalar', 'name');
        });
    }
};
