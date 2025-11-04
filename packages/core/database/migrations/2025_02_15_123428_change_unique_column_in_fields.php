<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasIndex('fields', 'fields_unique_parent')) {
            return;
        }

        Schema::table('fields', function (Blueprint $table) {
            $table->dropUnique(['model_type', 'model_key', 'slug']);

            $table->string('model_type', 191)->change();
            $table->string('model_key', 191)->change();
            $table->string('slug', 191)->change();

            $table->unique(['model_type', 'model_key', 'slug', 'parent_ulid'], 'fields_unique_parent');
        });
    }
};
