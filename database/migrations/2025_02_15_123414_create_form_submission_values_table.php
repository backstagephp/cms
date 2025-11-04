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
        if (Schema::hasTable('form_submission_values')) {
            return;
        }

        Schema::create('form_submission_values', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->foreignUlid('submission_ulid')->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('field_ulid')->nullable()->cascadeOnUpdate()->nullOnDelete();

            $table->longText('value');

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
