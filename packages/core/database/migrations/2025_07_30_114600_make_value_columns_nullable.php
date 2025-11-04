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
        if (Schema::hasColumn('content_field_values', 'value')) {
            Schema::table('content_field_values', function (Blueprint $table) {
                $table->longText('value')->nullable()->change();
            });
        }

        if (Schema::hasColumn('form_submission_values', 'value')) {
            Schema::table('form_submission_values', function (Blueprint $table) {
                $table->longText('value')->nullable()->change();
            });
        }
    }
};
