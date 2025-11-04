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
        if (Schema::hasTable('sites')) {
            return;
        }

        Schema::create('sites', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('title_separator')->nullable();
            $table->string('theme')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('logo')->nullable();
            $table->string('path')->nullable();
            $table->string('email_from_name')->nullable();
            $table->string('email_from_domain')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('auth')->default(false);
            $table->boolean('default')->default(false);
            $table->boolean('trailing_slash')->default(false);
            $table->timestamps();
        });

        Schema::create('site_user', function (Blueprint $table) {
            $table->foreignUlid('site_ulid');
            $table->foreignId('user_id');

            $table->index(['site_ulid', 'user_id']);
        });
    }
};
