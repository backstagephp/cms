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
        if (Schema::hasColumn('users', 'locale')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->char('locale', 5)->nullable()->after('password');
        });
    }
};
