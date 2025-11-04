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
        if (Schema::hasTable('relationables')) {
            return;
        }

        Schema::create('relationables', function (Blueprint $table) {
            $table->id();

            $table->string('relation_type');
            $table->ulid('relation_ulid');

            $table->string('related_type');
            $table->ulid('related_ulid');

            $table->index(['relation_type', 'relation_ulid']);
            $table->index(['related_type', 'related_ulid']);
        });
    }
};
