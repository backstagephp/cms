<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
            $table->ulid('ulid')->nullable()->after('slug');
        });

        DB::table('blocks')->whereNull('ulid')
            ->orderBy('slug')
            ->each(function ($block) {
                DB::table('blocks')
                    ->where('slug', $block->slug)
                    ->update(['ulid' => Str::ulid()]);
            });

        Schema::table('block_site', function (Blueprint $table) {
            $table->ulid('block_ulid')->nullable();
        });

        DB::statement('UPDATE block_site SET block_ulid = (SELECT ulid FROM blocks WHERE blocks.slug = block_site.block_slug)');

        Schema::table('block_site', function (Blueprint $table) {
            $table->dropIndex(['site_ulid', 'block_slug']);
        });

        Schema::table('block_site', function (Blueprint $table) {
            $table->dropColumn('block_slug');
        });

        if (Schema::hasTable('fields')) {
            $fields = DB::table('fields')
                ->where('model_type', 'block')
                ->get();

            foreach ($fields as $field) {
                $block = DB::table('blocks')
                    ->where('slug', $field->model_key)
                    ->first();

                DB::table('fields')
                    ->where('ulid', $field->ulid)
                    ->update(['model_key' => $block->ulid]);
            }
        }
    }
};
