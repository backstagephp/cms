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
        if (Schema::hasColumn('types', 'sort_column')) {
            return;
        }

        Schema::table('types', function (Blueprint $table) {
            $table->string('sort_column')->nullable()->after('body_field');
            $table->string('sort_direction')->nullable()->after('sort_column');
        });
    }
};
