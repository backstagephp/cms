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
        if (Schema::hasColumn('menu_items', 'parent_ulid')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropForeign(['parent_ulid']);
            });
        }
    }
};
