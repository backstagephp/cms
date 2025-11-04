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
        if (Schema::hasTable('form_actions')) {
            return;
        }

        Schema::create('form_actions', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->string('form_slug')->constrained(table: 'forms', column: 'slug')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignUlid('site_ulid')->nullable()->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 5);

            $table->string('type')->nullable();
            $table->json('config')->nullable();

            $table->timestamps();

            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
