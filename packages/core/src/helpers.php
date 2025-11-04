<?php

use Backstage\Models\Setting;

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        $keys = explode('.', $key);
        $setting = Setting::where('slug', $keys[0] ?? null)->with('fields')->first();

        if (! $setting) {
            return $default;
        }

        if (! isset($keys[1])) {
            return $setting->setting();
        }

        return $setting->setting($keys[1]) ?? $default;
    }
}
