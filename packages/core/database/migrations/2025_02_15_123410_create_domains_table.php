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
        if (Schema::hasTable('domains')) {
            return;
        }

        Schema::create('domains', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->ulid('alias_of')->nullable()->index();
            $table->string('environment')->nullable();
            $table->timestamps();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->foreign('alias_of')->references('ulid')->on('domains')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::create('domain_language', function (Blueprint $table) {
            $table->foreignUlid('domain_ulid')->constrained(table: 'domains', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 5);
            $table->string('path')->default('')->nullable();
            $table->primary(['domain_ulid', 'language_code']);
            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
