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
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->json('title_translatable')->nullable()->after('title');
            $table->json('body_translatable')->nullable()->after('body');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('broadcast_messages')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                DB::table('broadcast_messages')->where('id', $row->id)->update([
                    'title_translatable' => json_encode([$defaultLocale => $row->title]),
                    'body_translatable' => json_encode([$defaultLocale => $row->body]),
                ]);
            }
        });

        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->dropColumn(['title', 'body']);
        });

        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->renameColumn('title_translatable', 'title');
            $table->renameColumn('body_translatable', 'body');
        });
    }

    public function down(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->string('title_scalar', 255)->nullable()->after('title');
            $table->text('body_scalar')->nullable()->after('body');
        });

        $defaultLocale = Language::defaultLocale();

        DB::table('broadcast_messages')->orderBy('id')->chunkById(200, function ($rows) use ($defaultLocale) {
            foreach ($rows as $row) {
                $title = json_decode((string) $row->title, true) ?? [];
                $body = json_decode((string) $row->body, true) ?? [];

                DB::table('broadcast_messages')->where('id', $row->id)->update([
                    'title_scalar' => $title[$defaultLocale] ?? reset($title) ?: '',
                    'body_scalar' => $body[$defaultLocale] ?? reset($body) ?: '',
                ]);
            }
        });

        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->dropColumn(['title', 'body']);
        });

        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->renameColumn('title_scalar', 'title');
            $table->renameColumn('body_scalar', 'body');
        });
    }
};
