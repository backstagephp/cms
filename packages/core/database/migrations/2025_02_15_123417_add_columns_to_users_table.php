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
        if (Schema::hasColumn('users', 'current_site_ulid')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUlid('current_site_ulid')
                ->nullable()
                ->after('id')
                ->constrained(table: 'sites', column: 'ulid')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
