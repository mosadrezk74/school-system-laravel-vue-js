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
        Schema::table('assignments', function (Blueprint $table) {
            $table->json('title_translatable')->nullable()->after('title');
            $table->json('description_translatable')->nullable()->after('description');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('assignments')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                DB::table('assignments')->where('id', $row->id)->update([
                    'title_translatable' => json_encode([$defaultLocale => $row->title]),
                    'description_translatable' => $row->description !== null
                        ? json_encode([$defaultLocale => $row->description])
                        : null,
                ]);
            }
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->renameColumn('title_translatable', 'title');
            $table->renameColumn('description_translatable', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('title_scalar', 255)->nullable()->after('title');
            $table->text('description_scalar')->nullable()->after('description');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('assignments')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                $title = json_decode((string) $row->title, true) ?? [];
                $description = $row->description !== null ? (json_decode((string) $row->description, true) ?? []) : [];

                DB::table('assignments')->where('id', $row->id)->update([
                    'title_scalar' => $title[$defaultLocale] ?? reset($title) ?: '',
                    'description_scalar' => $description[$defaultLocale] ?? (reset($description) ?: null),
                ]);
            }
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->renameColumn('title_scalar', 'title');
            $table->renameColumn('description_scalar', 'description');
        });
    }
};
