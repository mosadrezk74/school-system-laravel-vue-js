<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, bool> */
    private array $tables = [
        'classes' => false,
        'classrooms' => false,
        'subjects' => true,
        'meetings' => true,
        'departments' => true,
    ];

    public function up(): void
    {
        foreach (array_keys($this->tables) as $table) {
            if (! Schema::hasColumn($table, 'branch_id')) {
                continue;
            }

            $foreignKeys = collect(Schema::getForeignKeys($table))
                ->filter(fn (array $key): bool => in_array('branch_id', $key['columns'], true))
                ->pluck('name');

            $indexes = collect(Schema::getIndexes($table))
                ->filter(fn (array $index): bool => in_array('branch_id', $index['columns'], true))
                ->pluck('name');

            Schema::table($table, function (Blueprint $blueprint) use ($foreignKeys, $indexes): void {
                foreach ($foreignKeys as $foreignKey) {
                    $blueprint->dropForeign($foreignKey);
                }

                foreach ($indexes as $index) {
                    $blueprint->dropIndex($index);
                }

                $blueprint->dropColumn('branch_id');
            });
        }

        if (! $this->hasIsActiveIndex()) {
            Schema::table('classrooms', function (Blueprint $blueprint): void {
                $blueprint->index(['is_active']);
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIsActiveIndex()) {
            Schema::table('classrooms', function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['is_active']);
            });
        }

        foreach ($this->tables as $table => $nullable) {
            if (Schema::hasColumn($table, 'branch_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($nullable): void {
                $column = $blueprint->unsignedBigInteger('branch_id');

                if ($nullable) {
                    $column->nullable();
                }

                $blueprint->index(['branch_id']);
            });
        }

        if (! Schema::hasTable('branches')) {
            return;
        }

        foreach ($this->tables as $table => $nullable) {
            Schema::table($table, function (Blueprint $blueprint) use ($nullable): void {
                $foreign = $blueprint->foreign('branch_id')->references('id')->on('branches');

                $nullable ? $foreign->nullOnDelete() : $foreign->cascadeOnDelete();
            });
        }
    }

    private function hasIsActiveIndex(): bool
    {
        return collect(Schema::getIndexes('classrooms'))
            ->contains(fn (array $index): bool => $index['columns'] === ['is_active']);
    }
};
