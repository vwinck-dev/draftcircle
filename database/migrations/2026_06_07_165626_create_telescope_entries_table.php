<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        $connection = config('telescope.storage.database.connection');

        return is_string($connection) ? $connection : null;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection(name: $this->getConnection());

        $schema->create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid(column: 'uuid');
            $table->uuid(column: 'batch_id');
            $table->string(column: 'family_hash')->nullable();
            $table->boolean(column: 'should_display_on_index')->default(true);
            $table->string(column: 'type', length: 20);
            $table->longText(column: 'content');
            $table->dateTime(column: 'created_at')->nullable();

            $table->unique(columns: 'uuid');
            $table->index(columns: 'batch_id');
            $table->index(columns: 'family_hash');
            $table->index(columns: 'created_at');
            $table->index(columns: ['type', 'should_display_on_index']);
        });

        $schema->create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid(column: 'entry_uuid');
            $table->string(column: 'tag');

            $table->primary(columns: ['entry_uuid', 'tag']);
            $table->index(columns: 'tag');

            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->cascadeOnDelete();
        });

        $schema->create('telescope_monitoring', function (Blueprint $table) {
            $table->string(column: 'tag')->primary();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection(name: $this->getConnection());

        $schema->dropIfExists(table: 'telescope_entries_tags');
        $schema->dropIfExists(table: 'telescope_entries');
        $schema->dropIfExists(table: 'telescope_monitoring');
    }
};
