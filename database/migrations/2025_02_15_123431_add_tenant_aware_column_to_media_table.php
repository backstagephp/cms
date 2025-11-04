<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn(app(\Backstage\Media\Models\Media::class)->getTable(), 'site_ulid')) {
            return;
        }

        Schema::table(app(\Backstage\Media\Models\Media::class)->getTable(), function (Blueprint $table) {
            $table->ulid('site_ulid')->nullable()->after('ulid');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn(app(\Backstage\Media\Models\Media::class)->getTable(), 'site_ulid')) {
            Schema::table(app(\Backstage\Media\Models\Media::class)->getTable(), function (Blueprint $table) {
                $table->dropColumn('site_ulid');
            });
        }
    }
};
