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
        if (! Schema::hasColumn('users', 'app_authentication_secret') && ! Schema::hasColumn('users', 'app_authentication_recovery_codes')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('app_authentication_secret')->nullable()->after('password');
                $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
            });
        }
    }
};
