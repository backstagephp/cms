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
        if (Schema::hasColumn('types', 'parent_required') && Schema::hasColumn('types', 'parent_filters')) {
            return;
        }

        Schema::table('types', function (Blueprint $table) {
            $table->boolean('parent_required')->default(false)->after('public');
            $table->json('parent_filters')->nullable()->after('parent_required');
        });
    }
};
