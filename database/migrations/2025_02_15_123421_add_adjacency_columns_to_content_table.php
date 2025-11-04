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
        if (Schema::hasColumn('content', 'position')) {
            return;
        }

        Schema::table('content', function (Blueprint $table) {
            $table->integer('position')->nullable()->after('lock');
            $table->integer('depth')->nullable()->after('position');
        });
    }
};
