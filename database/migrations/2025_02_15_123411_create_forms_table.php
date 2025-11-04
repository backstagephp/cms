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
        if (Schema::hasTable('forms')) {
            return;
        }

        Schema::create('forms', function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->foreignUlid('site_ulid')->nullable()->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_field')->nullable();
            $table->string('submit_button')->nullable();

            $table->timestamps();
        });
    }
};
