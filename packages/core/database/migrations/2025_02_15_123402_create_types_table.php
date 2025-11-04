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
        if (Schema::hasTable('types')) {
            return;
        }

        Schema::create('types', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');
            $table->string('name_plural');
            $table->string('icon');
            $table->string('name_field')->nullable();
            $table->string('body_field')->nullable();
            $table->boolean('public')->default(false);

            $table->timestamps();
        });

        Schema::create('site_type', function (Blueprint $table) {
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('type_slug');
            $table->foreign('type_slug')->references('slug')->on('types')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['site_ulid', 'type_slug']);
        });
    }
};
