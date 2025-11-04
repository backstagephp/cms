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
        if (Schema::hasColumn('redirects', 'site_id')) {
            return;
        }

        Schema::table('redirects', function (Blueprint $table) {
            $table->ulid('site_id')->nullable()->after('ulid');
        });
    }
};
