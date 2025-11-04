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
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->string('slug')->unique();
            $table->foreignUlid('site_ulid')->nullable()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->char('language_code', 5)->nullable();
            $table->string('name');
            $table->json('values')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
