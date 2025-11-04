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
        if (Schema::hasTable('blocks')) {
            return;
        }

        Schema::create('blocks', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');
            $table->string('name_field')->nullable();
            $table->string('icon');
            $table->string('component')->nullable();

            $table->timestamps();
        });

        Schema::create('block_site', function (Blueprint $table) {
            $table->foreignUlid('site_ulid');

            $table->string('block_slug');

            $table->index(['site_ulid', 'block_slug']);
        });
    }
};
