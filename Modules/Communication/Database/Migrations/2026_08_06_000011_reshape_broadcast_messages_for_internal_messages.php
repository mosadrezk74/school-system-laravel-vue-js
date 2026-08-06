<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->renameColumn('created_by', 'sender_id');
            $table->dropColumn('channel');
        });
    }

    public function down(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->renameColumn('sender_id', 'created_by');
            $table->string('channel', 10)->after('body');
        });
    }
};
