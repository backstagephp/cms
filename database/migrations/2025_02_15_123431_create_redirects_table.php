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
        if (Schema::hasTable('redirects')) {
            return;
        }

        Schema::create('redirects', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->string('source');
            $table->string('destination')->nullable();
            $table->unsignedInteger('code', 3)->default(301)->autoIncrement(false);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamps();
        });
    }
};
