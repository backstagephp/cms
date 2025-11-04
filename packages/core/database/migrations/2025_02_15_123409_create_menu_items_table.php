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
        if (Schema::hasTable('menu_items')) {
            return;
        }

        Schema::create('menu_items', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->string('menu_slug')->references('slug')->on('menu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('parent_ulid')->nullable()->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->nullOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->string('title');
            $table->boolean('active');

            $table->string('url')->nullable();
            $table->string('target')->nullable();

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
