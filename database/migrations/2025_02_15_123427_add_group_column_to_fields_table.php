<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('fields', 'group')) {
            return;
        }

        Schema::table('fields', function (Blueprint $table) {
            $table->string('group')->nullable()->after('position');
        });
    }
};
