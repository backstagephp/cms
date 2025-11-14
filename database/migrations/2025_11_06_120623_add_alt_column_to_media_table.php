<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $model = config('backstage.media.model');
        Schema::table((new $model)->getTable(), function (Blueprint $table) {
            $table->text('alt')->after('height');
        });
    }

    public function down(): void
    {
        $model = config('backstage.media.model');
        Schema::table((new $model)->getTable(), function (Blueprint $table) {
            $table->dropColumn('alt');
        });
    }
};
