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
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign('menu_items_parent_ulid_foreign');
        });
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreign('parent_ulid')
                ->references('ulid')
                ->on('menu_items');
        });
    }
};
