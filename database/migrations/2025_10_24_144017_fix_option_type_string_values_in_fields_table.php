<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fields = DB::table('fields')
            ->whereNotNull('config')
            ->where('config', 'like', '%optionType%')
            ->get();

        foreach ($fields as $field) {
            $config = json_decode($field->config, true);

            if (isset($config['optionType']) && is_string($config['optionType'])) {
                $config['optionType'] = [$config['optionType']];

                DB::table('fields')
                    ->where('ulid', $field->ulid)
                    ->update(['config' => json_encode($config)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $fields = DB::table('fields')
            ->whereNotNull('config')
            ->where('config', 'like', '%optionType%')
            ->get();

        foreach ($fields as $field) {
            $config = json_decode($field->config, true);

            if (isset($config['optionType']) && is_array($config['optionType']) && count($config['optionType']) === 1) {
                $config['optionType'] = $config['optionType'][0];

                DB::table('fields')
                    ->where('ulid', $field->ulid)
                    ->update(['config' => json_encode($config)]);
            }
        }
    }
};
