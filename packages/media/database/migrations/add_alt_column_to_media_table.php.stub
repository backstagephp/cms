<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         Schema::table(app(config('backstage.media.model'))->getTable(), function (Blueprint $table) {
            $table->text('alt')->after('height');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(app(config('backstage.media.model'))->getTable());
    }
};