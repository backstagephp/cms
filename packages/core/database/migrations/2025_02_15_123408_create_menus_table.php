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
        if (Schema::hasTable('menus')) {
            return;
        }

        Schema::create('menus', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 5);

            $table->string('name');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
