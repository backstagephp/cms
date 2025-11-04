<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tags')) {
            return;
        }

        Schema::create('tags', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->string('name');
            $table->string('slug');
            $table->integer('position')->nullable();

            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignUlid('tag_ulid')->constrained(table: 'tags', column: 'ulid')->cascadeOnDelete();

            $table->string('taggable_type');
            $table->ulid('taggable_ulid');

            $table->index(['taggable_type', 'taggable_ulid']);
            $table->unique(['tag_ulid', 'taggable_ulid', 'taggable_type']);
        });
    }
};
