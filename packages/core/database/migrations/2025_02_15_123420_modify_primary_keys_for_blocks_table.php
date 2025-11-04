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
        if (Schema::hasColumn('blocks', 'ulid')) {
            return;
        }

        Schema::table('blocks', function (Blueprint $table) {
            $table->dropPrimary();
            $table->ulid('ulid')->primary()->change();
        });

        Schema::table('block_site', function (Blueprint $table) {
            $table->ulid('block_ulid')->change();
            $table->foreign('block_ulid')->references('ulid')->on('blocks');
        });
    }
};
